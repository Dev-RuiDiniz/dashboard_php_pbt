# Dashboard PHP PBT - Sistema Igreja Social

Sistema web em PHP + MySQL para operacao social da igreja, com controle de familias, PROJETO AMOR, entregas, equipamentos, visitas, usuarios e relatorios.

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

## Skill local do Codex
- Skill instalada localmente: `C:\Users\RUI FRANCISCO\.codex\skills\maintain-dashboard-php-pbt`
- Guia versionado no repositório: `docs/CODEX_LOCAL_SKILL.md`
- Exemplo de uso: `Use $maintain-dashboard-php-pbt to extend or debug this custom PHP dashboard.`

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
  - detalhe da familia com abas: composicao familiar, resumo, entregas, emprestimos, visitas/anotacoes e pendencias
  - cadastro unificado de pessoas da familia (principal, membro e crianca) na primeira aba do detalhe
  - indicadores com renda total e media per capita por familia
  - RG opcional no cadastro do responsavel, membros e criancas
  - data de cadastro e ultima atualizacao visiveis
  - alerta de documentacao pendente e alerta de visita
  - composicao familiar com `Estuda?` para criancas e adultos, e `Trabalha?` para maiores de idade
  - campos de saude e beneficio social no cadastro base do responsavel
- Criancas
  - cadastro centralizado na aba de detalhe da familia
- PROJETO AMOR
  - CRUD completo
  - mantem rota tecnica `/people`
  - idade calculada automaticamente a partir da data de nascimento
  - telefone, endereco anterior, data de cadastro e ultima atualizacao visiveis
  - campos de saude e beneficio social no cadastro base
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
  - bloqueio mensal considerando apenas registros ja baixados como `retirou`
  - mensagens operacionais mais claras para duplicidade, bloqueio mensal e limite de cestas
- Equipamentos
  - CRUD completo
- Emprestimos de equipamentos
  - criar, listar, devolver, excluir
  - novos tipos: `cadeira_banho`, `equipamentos_enfermaria`, `bengala_quatro_pes`, `bota_ortopedica_dortler`, `tipoia`
  - snapshot da retirada com responsavel, telefone, CPF, endereco e usuario do equipamento
  - devolucao ruim gera alerta visivel, registro de manutencao e equipamento `inativo`
  - retorno apos manutencao libera o equipamento novamente para `disponivel`
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

## Atualizacoes operacionais de 26/03/2026
- Interface do modulo de moradores de rua renomeada para `PROJETO AMOR`, preservando as rotas internas.
- `Nova Familia` e `Nova Pessoa` agora exibem datas de cadastro e atualizacao.
- O sistema aceita salvar familia, membro e crianca sem RG, validando o formato apenas quando o campo for preenchido.
- Cadastro base de familia e PROJETO AMOR passou a registrar doenca cronica, deficiencia fisica, medicacao continua e beneficio social.
- Emprestimos de equipamento passaram a distinguir claramente quem retirou o item e quem vai usa-lo.
- Estado de conservacao `ruim` agora gera inativacao do equipamento ate a conclusao da manutencao.

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
# banco novo:
# importar database/final_mvp.sql no MySQL
#
# banco ja existente:
# php database/migrate.php
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
- banco novo: importar `database/final_mvp.sql`
- banco existente: rodar `php database/migrate.php` ou aplicar as migrations pendentes
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

## Observacao de ambiente local
- Se o projeto estiver sem `vendor/`, execute `composer install` antes de subir `public/index.php`.
- No ambiente avaliado nesta implementacao, o PHP local nao possui extensao `openssl`; isso impede instalar dependencias pelo Composer nessa maquina sem ajuste do PHP.

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
