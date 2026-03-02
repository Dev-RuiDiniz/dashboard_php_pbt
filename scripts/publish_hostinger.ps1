param(
    [Parameter(Mandatory = $true)]
    [string]$AppUrl,
    [Parameter(Mandatory = $true)]
    [string]$DbHost,
    [int]$DbPort = 3306,
    [Parameter(Mandatory = $true)]
    [string]$DbName,
    [Parameter(Mandatory = $true)]
    [string]$DbUser,
    [Parameter(Mandatory = $true)]
    [string]$DbPass
)

$ErrorActionPreference = "Stop"
Set-StrictMode -Version Latest

function Step([string]$message) {
    Write-Host ""
    Write-Host "==> $message" -ForegroundColor Cyan
}

function Assert-Ok([bool]$condition, [string]$message) {
    if (-not $condition) {
        throw $message
    }
}

$repoRoot = (Resolve-Path (Join-Path $PSScriptRoot "..")).Path
Set-Location $repoRoot

$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$distRoot = Join-Path $repoRoot "dist"
$staging = Join-Path $distRoot "hostinger_release_$timestamp"
$zipPath = Join-Path $distRoot "hostinger_release_$timestamp.zip"
$reportPath = Join-Path $distRoot "hostinger_release_$timestamp.checklist.txt"

Step "Validando pre-requisitos"
$composer = Get-Command composer -ErrorAction SilentlyContinue
$php = Get-Command php -ErrorAction SilentlyContinue
Assert-Ok ($null -ne $composer) "Composer nao encontrado no PATH."
Assert-Ok ($null -ne $php) "PHP nao encontrado no PATH."
Assert-Ok (Test-Path "composer.json") "composer.json nao encontrado na raiz do projeto."
Assert-Ok (Test-Path "public/index.php") "public/index.php nao encontrado."
Assert-Ok (Test-Path "public/.htaccess") "public/.htaccess nao encontrado."
Assert-Ok (Test-Path "database/final_mvp.sql") "database/final_mvp.sql nao encontrado."
Assert-Ok (Test-Path "docs/MANUAL_CLIENTE.pdf") "docs/MANUAL_CLIENTE.pdf nao encontrado."

Step "Build de producao (composer install --no-dev)"
composer install --no-dev --optimize-autoloader --classmap-authoritative

Step "Lint PHP (projeto)"
$phpFiles = Get-ChildItem -Path $repoRoot -Recurse -File -Filter *.php |
    Where-Object { $_.FullName -notmatch "\\vendor\\" -and $_.FullName -notmatch "\\.git\\" }

foreach ($file in $phpFiles) {
    $out = & php -l $file.FullName 2>&1
    if ($LASTEXITCODE -ne 0 -or $out -notmatch "No syntax errors detected") {
        throw "Lint falhou em: $($file.FullName)`n$out"
    }
}

Step "Validacao runtime (/health)"
$serverJob = Start-Job -ScriptBlock {
    param($root)
    Set-Location $root
    php -S 127.0.0.1:8110 -t public | Out-Null
} -ArgumentList $repoRoot

Start-Sleep -Seconds 2
try {
    $health = Invoke-WebRequest -Uri "http://127.0.0.1:8110/health" -UseBasicParsing -TimeoutSec 10
    Assert-Ok ($health.StatusCode -eq 200) "Healthcheck nao retornou HTTP 200."
    Assert-Ok ($health.Content -match '"status":"ok"') "Healthcheck nao retornou status ok."
} finally {
    Stop-Job $serverJob -ErrorAction SilentlyContinue | Out-Null
    Remove-Job $serverJob -ErrorAction SilentlyContinue | Out-Null
}

Step "Montando .env de producao"
$prodEnv = @(
    "APP_NAME=`"Dashboard PHP PBT`""
    "APP_ENV=production"
    "APP_DEBUG=false"
    "APP_URL=$AppUrl"
    "ALERT_STALE_DAYS=30"
    "AUTH_MAX_LOGIN_ATTEMPTS=5"
    "AUTH_LOCK_MINUTES=15"
    "AUTH_RESET_TOKEN_TTL_MINUTES=60"
    "CEP_CORREIOS_BASE_URL=https://api.correios.com.br/cep/v2"
    "CEP_CORREIOS_BEARER_TOKEN="
    "CEP_ENABLE_VIACEP_FALLBACK=true"
    "CEP_LOOKUP_TIMEOUT=6"
    ""
    "DB_CONNECTION=mysql"
    "DB_HOST=$DbHost"
    "DB_PORT=$DbPort"
    "DB_NAME=$DbName"
    "DB_USER=$DbUser"
    "DB_PASS=$DbPass"
    "DB_CHARSET=utf8mb4"
) -join "`r`n"

if (-not (Test-Path $distRoot)) {
    New-Item -Path $distRoot -ItemType Directory | Out-Null
}
if (Test-Path $staging) {
    Remove-Item -Path $staging -Recurse -Force
}
New-Item -Path $staging -ItemType Directory | Out-Null
$tmpEnvPath = Join-Path $distRoot ".env.generated.$timestamp"
$prodEnv | Set-Content -Path $tmpEnvPath -Encoding UTF8

Step "Gerando pacote de publicacao"
$excludeDirs = @(".git", ".github", ".local", "dist")
$excludeFiles = @(".env")

Get-ChildItem -Path $repoRoot -Force | ForEach-Object {
    if ($excludeDirs -contains $_.Name) { return }
    if ($excludeFiles -contains $_.Name) { return }
    Copy-Item -Path $_.FullName -Destination $staging -Recurse -Force
}
Copy-Item -Path $tmpEnvPath -Destination (Join-Path $staging ".env") -Force
Remove-Item -Path $tmpEnvPath -Force

if (Test-Path $zipPath) {
    Remove-Item -Path $zipPath -Force
}
Compress-Archive -Path (Join-Path $staging "*") -DestinationPath $zipPath -Force

Step "Checklist final"
$checklist = @(
    "Data: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
    "Build composer sem dev: OK"
    "Lint PHP: OK"
    "Healthcheck /health: OK"
    "public/.htaccess: OK"
    "database/final_mvp.sql: OK"
    "Manual PDF (docs/MANUAL_CLIENTE.pdf): OK"
    "Pacote zip gerado: $zipPath"
    "Proximo passo Hostinger:"
    "1) Enviar zip e extrair no servidor"
    "2) Garantir Document Root em public/"
    "3) Importar database/final_mvp.sql no MySQL"
    "4) Validar https://SEU_DOMINIO/health"
) -join "`r`n"

$checklist | Set-Content -Path $reportPath -Encoding UTF8
Write-Host ""
Write-Host "PUBLICACAO PRONTA" -ForegroundColor Green
Write-Host "ZIP: $zipPath"
Write-Host "CHECKLIST: $reportPath"
