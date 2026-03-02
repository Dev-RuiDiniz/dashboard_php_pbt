$ErrorActionPreference = 'Stop'

$baseUrl = 'http://127.0.0.1:8102'
$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$tag = [guid]::NewGuid().ToString('N').Substring(0, 8)
$today = Get-Date -Format 'yyyy-MM-dd'
$due = (Get-Date).AddDays(15).ToString('yyyy-MM-dd')
$results = New-Object System.Collections.Generic.List[string]

function Assert-True([bool]$condition, [string]$message) {
    if (-not $condition) {
        throw "FALHA: $message"
    }
}

function Get-FirstMatch([string]$content, [string]$pattern, [string]$label) {
    $m = [regex]::Match($content, $pattern, 'Singleline')
    if (-not $m.Success) {
        throw "FALHA: nao encontrou $label"
    }
    return $m.Groups[1].Value
}

# Login
$null = Invoke-WebRequest -Uri "$baseUrl/login" -WebSession $session -UseBasicParsing
$loginResp = Invoke-WebRequest -Uri "$baseUrl/login" -Method Post -Body @{ email='admin@igrejasocial.local'; password='admin123' } -WebSession $session -UseBasicParsing
Assert-True ($loginResp.Content -match 'Dashboard|Painel|Familias|Visitas') 'login admin'
$results.Add('Login admin: OK')

# Families CRUD
$familyName = "Teste Familia $tag"
$familyNameUpdated = "Teste Familia $tag Atualizada"
$null = Invoke-WebRequest -Uri "$baseUrl/families" -Method Post -Body @{ responsible_name=$familyName; city='Sao Paulo'; state='SP'; is_active='1' } -WebSession $session -UseBasicParsing
$familiesPage = Invoke-WebRequest -Uri "$baseUrl/families?q=$([uri]::EscapeDataString($familyName))" -WebSession $session -UseBasicParsing
$familyId = Get-FirstMatch $familiesPage.Content "/families/show\?id=(\d+)" 'family id'
$null = Invoke-WebRequest -Uri "$baseUrl/families/update?id=$familyId" -Method Post -Body @{ responsible_name=$familyNameUpdated; city='Sao Paulo'; state='SP'; is_active='1' } -WebSession $session -UseBasicParsing
$checkUpdated = Invoke-WebRequest -Uri "$baseUrl/families?q=$([uri]::EscapeDataString($familyNameUpdated))" -WebSession $session -UseBasicParsing
Assert-True ($checkUpdated.Content -match [regex]::Escape($familyNameUpdated)) 'family update'
$results.Add('Familias CRUD: OK')

# People CRUD + Social Record CRUD
$personName = "Teste Pessoa $tag"
$personNameUpdated = "Teste Pessoa $tag Atualizada"
$null = Invoke-WebRequest -Uri "$baseUrl/people" -Method Post -Body @{ full_name=$personName } -WebSession $session -UseBasicParsing
$peoplePage = Invoke-WebRequest -Uri "$baseUrl/people?q=$([uri]::EscapeDataString($personName))" -WebSession $session -UseBasicParsing
$personId = Get-FirstMatch $peoplePage.Content "/people/show\?id=(\d+)" 'person id'
$null = Invoke-WebRequest -Uri "$baseUrl/people/update?id=$personId" -Method Post -Body @{ full_name=$personNameUpdated } -WebSession $session -UseBasicParsing
$personCheck = Invoke-WebRequest -Uri "$baseUrl/people?q=$([uri]::EscapeDataString($personNameUpdated))" -WebSession $session -UseBasicParsing
Assert-True ($personCheck.Content -match [regex]::Escape($personNameUpdated)) 'person update'

$null = Invoke-WebRequest -Uri "$baseUrl/people/social-records?person_id=$personId" -Method Post -Body @{
    consent_text_version='v1.0'
    consent_name='Consentimento Teste'
    immediate_needs='Necessidade Inicial'
} -WebSession $session -UseBasicParsing
$personShow = Invoke-WebRequest -Uri "$baseUrl/people/show?id=$personId" -WebSession $session -UseBasicParsing
$recordId = Get-FirstMatch $personShow.Content 'Atendimento #(\d+)' 'social record id'

$null = Invoke-WebRequest -Uri "$baseUrl/people/social-records/update?id=$recordId&person_id=$personId" -Method Post -Body @{
    consent_text_version='v1.1'
    consent_name='Consentimento Teste Atualizado'
    immediate_needs='Necessidade Atualizada'
} -WebSession $session -UseBasicParsing
$recordUpdatedPage = Invoke-WebRequest -Uri "$baseUrl/people/show?id=$personId" -WebSession $session -UseBasicParsing
Assert-True ($recordUpdatedPage.Content -match 'Necessidade Atualizada|Consentimento Teste Atualizado') 'social record update'

$null = Invoke-WebRequest -Uri "$baseUrl/people/social-records/delete?id=$recordId&person_id=$personId" -Method Post -WebSession $session -UseBasicParsing
$results.Add('Pessoas CRUD + Ficha Social U/D: OK')

# Delivery event + delivery delete
$deliveryFamilyName = "Teste Entrega Familia $tag"
$null = Invoke-WebRequest -Uri "$baseUrl/families" -Method Post -Body @{ responsible_name=$deliveryFamilyName; city='Sao Paulo'; state='SP'; is_active='1' } -WebSession $session -UseBasicParsing
$deliveryFamilyPage = Invoke-WebRequest -Uri "$baseUrl/families?q=$([uri]::EscapeDataString($deliveryFamilyName))" -WebSession $session -UseBasicParsing
$deliveryFamilyId = Get-FirstMatch $deliveryFamilyPage.Content "/families/show\?id=(\d+)" 'delivery family id'

$eventName = "Teste Evento $tag"
$null = Invoke-WebRequest -Uri "$baseUrl/delivery-events" -Method Post -Body @{ name=$eventName; event_date=$today; status='rascunho'; block_multiple_same_month='1' } -WebSession $session -UseBasicParsing
$eventsPage = Invoke-WebRequest -Uri "$baseUrl/delivery-events?q=$([uri]::EscapeDataString($eventName))" -WebSession $session -UseBasicParsing
$eventId = Get-FirstMatch $eventsPage.Content "/delivery-events/show\?id=(\d+)" 'event id'

$null = Invoke-WebRequest -Uri "$baseUrl/delivery-events/deliveries?event_id=$eventId" -Method Post -Body @{ target_type='family'; family_id=$deliveryFamilyId; quantity='1'; observations='Teste' } -WebSession $session -UseBasicParsing
$eventShow = Invoke-WebRequest -Uri "$baseUrl/delivery-events/show?id=$eventId" -WebSession $session -UseBasicParsing
$deliveryId = Get-FirstMatch $eventShow.Content '/delivery-events/deliveries/delete\?id=(\d+)(?:&amp;|&)event_id=' 'delivery id'
$null = Invoke-WebRequest -Uri "$baseUrl/delivery-events/deliveries/delete?id=$deliveryId&event_id=$eventId" -Method Post -WebSession $session -UseBasicParsing
$null = Invoke-WebRequest -Uri "$baseUrl/delivery-events/delete?id=$eventId" -Method Post -WebSession $session -UseBasicParsing
$eventsCheck = Invoke-WebRequest -Uri "$baseUrl/delivery-events" -WebSession $session -UseBasicParsing
Assert-True (-not ($eventsCheck.Content -match "/delivery-events/show\?id=$eventId")) 'delivery event delete'
$results.Add('Eventos de entrega + lista operacional delete: OK')

# Equipment loan delete
$null = Invoke-WebRequest -Uri "$baseUrl/equipment" -Method Post -Body @{ type='muleta'; condition_state='bom'; status='disponivel'; notes='equip teste e2e' } -WebSession $session -UseBasicParsing
$equipmentPage = Invoke-WebRequest -Uri "$baseUrl/equipment" -WebSession $session -UseBasicParsing
$equipmentId = Get-FirstMatch $equipmentPage.Content '/equipment/edit\?id=(\d+)' 'equipment id'
$null = Invoke-WebRequest -Uri "$baseUrl/equipment-loans" -Method Post -Body @{ equipment_id=$equipmentId; target_type='family'; family_id=$deliveryFamilyId; loan_date=$today; due_date=$due; notes='Emprestimo teste' } -WebSession $session -UseBasicParsing
$loansAfterCreate = Invoke-WebRequest -Uri "$baseUrl/equipment-loans" -WebSession $session -UseBasicParsing
$loanId = Get-FirstMatch $loansAfterCreate.Content '/equipment-loans/delete\?id=(\d+)' 'loan id'
$null = Invoke-WebRequest -Uri "$baseUrl/equipment-loans/delete?id=$loanId" -Method Post -WebSession $session -UseBasicParsing
$null = Invoke-WebRequest -Uri "$baseUrl/equipment/delete?id=$equipmentId" -Method Post -WebSession $session -UseBasicParsing
$results.Add('Emprestimos delete: OK')

# Users create/delete
$userEmail = "teste.$tag@local.test"
$null = Invoke-WebRequest -Uri "$baseUrl/users" -Method Post -Body @{ name="Usuario Teste $tag"; email=$userEmail; role='viewer'; password='Senha12345'; is_active='1' } -WebSession $session -UseBasicParsing
$usersPage = Invoke-WebRequest -Uri "$baseUrl/users" -WebSession $session -UseBasicParsing
$userDeletePattern = '/users/delete\?id=(\d+)[\s\S]*?' + [regex]::Escape($userEmail)
$mUser = [regex]::Match($usersPage.Content, $userDeletePattern, 'Singleline')
if (-not $mUser.Success) {
    $mUser = [regex]::Match($usersPage.Content, [regex]::Escape($userEmail) + '[\s\S]*?/users/delete\?id=(\d+)', 'Singleline')
}
Assert-True $mUser.Success 'user id for delete'
$userId = $mUser.Groups[1].Value
$null = Invoke-WebRequest -Uri "$baseUrl/users/delete?id=$userId" -Method Post -WebSession $session -UseBasicParsing
$usersCheck = Invoke-WebRequest -Uri "$baseUrl/users" -WebSession $session -UseBasicParsing
Assert-True (-not ($usersCheck.Content -match [regex]::Escape($userEmail))) 'user delete'
$results.Add('Usuarios create/delete: OK')

# Cleanup created family/person records
$null = Invoke-WebRequest -Uri "$baseUrl/people/delete?id=$personId" -Method Post -WebSession $session -UseBasicParsing
$null = Invoke-WebRequest -Uri "$baseUrl/families/delete?id=$familyId" -Method Post -WebSession $session -UseBasicParsing
$null = Invoke-WebRequest -Uri "$baseUrl/families/delete?id=$deliveryFamilyId" -Method Post -WebSession $session -UseBasicParsing

$results.Add('Cleanup de dados de teste: OK')
$results | ForEach-Object { Write-Output $_ }
Write-Output 'RESULTADO_FINAL: APROVADO'
