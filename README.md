# Dashboard PHP PBT - Sistema Igreja Social

Sistema web em PHP + MySQL para operacao social da igreja, com controle de familias, pessoas acompanhadas, entregas, equipamentos, visitas, usuarios e relatorios.

## Stack e requisitos
- PHP `8.2+`
- MySQL/MariaDB
- Composer
- Extensoes PHP: `pdo_mysql`, `mbstring`, `openssl`, `json`

## Estrutura principal
- `public/` front controller e assets
- `app/` controllers, models, services, views
- `config/` rotas e configuracoes
- `database/` schema, migrations e seed
- `docs/` documentacao operacional e deploy

## Modulos do sistema
- Autenticacao e seguranca
  - login/logout
  - bloqueio por tentativas de login
  - recuperacao de senha por token
  - auditoria em `audit_logs`
- Dashboard
  - indicadores operacionais e atalhos
- Familias
  - CRUD de familias
  - cadastro unificado de pessoas da familia (membro, dependente e crianca) na aba de detalhe
- Criancas
  - cadastro centralizado na aba de detalhe da familia
- Pessoas acompanhadas
  - CRUD completo
- Fichas sociais (dentro de pessoa)
  - CRUD completo
- Encaminhamentos (dentro de pessoa)
  - CRUD completo
- Acompanhamento espiritual (dentro de pessoa)
  - CRUD completo
- Entregas
  - CRUD de eventos
  - CRUD da lista operacional (convidados/entregas)
  - fluxo de status `nao_veio -> presente -> retirou`
  - fechar/reabrir evento, CSV e impressao
- Equipamentos
  - CRUD completo
- Emprestimos de equipamentos
  - criar, listar, devolver, excluir
- Visitas
  - CRUD completo + concluir visita
- Relatorios
  - filtros e exportacoes PDF/CSV/Excel
- Usuarios (admin)
  - criar, listar, editar, ativar/desativar, excluir (com regras de seguranca)

## Perfis de acesso (RBAC)
- `admin`: acesso total
- `voluntario`: operacao social + gestao operacional (sem modulo de pessoas/fichas sociais)
- `pastoral`: leitura geral + gestao de visitas (sem modulo de pessoas/fichas sociais)
- `viewer`: somente leitura

## Rotas principais
- `/login`
- `/dashboard`
- `/families`
- `/children` (compatibilidade: redireciona para `Familias`)
- `/people` (admin)
- `/social-records` (admin)
- `/delivery-events`
- `/equipment`
- `/equipment-loans`
- `/visits`
- `/reports`
- `/users` (admin)
- `/health`

## Setup local rapido
1. Instalar dependencias:
```bash
composer install
```
2. Configurar ambiente:
```bash
copy .env.example .env
```
3. Ajustar credenciais de banco no `.env`.
4. Criar schema/dados iniciais:
```bash
# primeiro deploy local:
# importar database/final_mvp.sql no MySQL
```
5. Subir servidor local:
```bash
composer run serve
```

## Deploy (Hostinger)
Checklist completo em:
- `docs/DEPLOY_HOSTINGER_CHECKLIST.md`

Resumo:
- apontar Document Root para `public/`
- configurar `.env` de producao (`APP_ENV=production`, `APP_DEBUG=false`)
- importar `database/final_mvp.sql`
- validar `/health`

## Manual do cliente
- Markdown: `docs/MANUAL_CLIENTE.md`
- PDF: `docs/MANUAL_CLIENTE.pdf`

## Estado dos testes (02/03/2026)
- `OK`: lint PHP completo, rotas compilando, servidor subindo, `/health` e `/login` respondendo `200`.
- `OK`: teste funcional E2E autenticado (CRUD por abas) aprovado, cobrindo:
  - login admin
  - familias (`create/read/update/delete`)
  - pessoas (`create/read/update/delete`)
  - ficha social (`create/update/delete`)
  - eventos de entrega e lista operacional (`create/delete`)
  - emprestimos de equipamentos (`create/delete`)
  - usuarios (`create/delete`)

## Pronto para producao
- Template de ambiente de producao: `.env.production.example`
- Checklist de deploy Hostinger: `docs/DEPLOY_HOSTINGER_CHECKLIST.md`
- Relatorio de prontidao: `docs/RELATORIO_PRONTIDAO_HOSTINGER.md`
- Manual do cliente (PDF): `docs/MANUAL_CLIENTE.pdf`

## Publicacao em comando unico (Hostinger)
Use o script abaixo para executar build, validacoes finais e gerar o `.zip` pronto para upload:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\publish_hostinger.ps1 -AppUrl "https://SEU_DOMINIO" -DbHost "127.0.0.1" -DbPort 3306 -DbName "SEU_BANCO" -DbUser "SEU_USUARIO" -DbPass "SUA_SENHA"
```

Saida:
- pacote: `dist/hostinger_release_YYYYMMDD_HHMMSS.zip`
- checklist final: `dist/hostinger_release_YYYYMMDD_HHMMSS.checklist.txt`
