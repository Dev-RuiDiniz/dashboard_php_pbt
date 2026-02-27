# Plano do Projeto - Dashboard PHP PBT (Igreja Social)

## 1. Objetivo do projeto

Criar um sistema web para gestão de acao social da igreja, substituindo fichas em papel e centralizando:

- cadastro de familias e membros
- cadastro de pessoas acompanhadas (ficha social)
- criancas vinculadas
- entregas de cestas
- equipamentos de mobilidade (emprestimos/devolucoes)
- visitas e pendencias
- relatorios, exportacoes e dashboard

Baseado na especificacao do arquivo Word `Especificacao_Sistema_Igreja_Social_PHP_MySQL.docx`.

## 2. Stack e ambiente alvo

- Backend: PHP 8.1+ (preferencia 8.2)
- Banco: MySQL/MariaDB (Hostinger)
- Acesso ao banco: PDO + prepared statements
- Frontend: HTML + Bootstrap (renderizacao server-side)
- JS: minimo necessario (mascaras e validacoes)
- Exportacao: mPDF (PDF)
- Arquitetura: MVC simples

## 3. Regras gerais do projeto

### 3.1 Padrao tecnico

- Usar arquitetura MVC simples e organizada por modulos.
- Separar regras de negocio em `Services/`.
- Nao acessar banco direto nas `Views`.
- Toda query deve usar prepared statements.
- Validacao de entrada no servidor e, quando util, no front.

### 3.2 Seguranca

- Senhas com hash forte (`password_hash` / bcrypt ou argon2).
- Sessao segura (regenerar session id no login, cookies `HttpOnly` e `SameSite`).
- Controle de acesso por perfil (RBAC) em cada rota/acao.
- Auditoria para login, exportacao, criacao, edicao e exclusao.

### 3.3 LGPD e consentimento

- Ficha social exige consentimento digital obrigatorio.
- Exportacoes devem ser restritas por permissao.
- Dados sensiveis com acesso por perfil e modulo.

### 3.4 Qualidade e entrega

- Entregar por sprints curtas e incrementais.
- Cada sprint deve terminar com algo utilizavel/testavel.
- Priorizar MVP operacional antes de refinamentos visuais.

## 4. Perfis e permissoes (RBAC)

- `admin`: acesso total, usuarios, configuracoes e auditoria
- `voluntario`: operacao (cadastros, entregas, equipamentos, visitas)
- `pastoral`: leitura/registros ligados ao acompanhamento espiritual
- `viewer` (opcional): somente leitura de dashboard e relatorios

Permissoes por:

- modulo (`familias`, `fichas`, `entregas`, `equipamentos`, `relatorios`, etc.)
- acao (`ver`, `criar`, `editar`, `excluir`, `exportar`)

## 5. Modulos do sistema (escopo funcional)

1. Autenticacao
2. Dashboard operacional
3. Familias
4. Pessoas acompanhadas (ficha social)
5. Criancas
6. Entregas de cestas (eventos + lista operacional)
7. Equipamentos de mobilidade (cadastro + emprestimos)
8. Visitas e pendencias
9. Usuarios e configuracoes (admin)
10. Relatorios e exportacoes

## 6. Estrutura sugerida do projeto

```text
public/
  index.php
  assets/

app/
  Controllers/
  Models/
  Services/
  Views/
  Middlewares/

config/
  database.php
  routes.php

storage/
  logs/
  exports/
  uploads/

database/
  schema.sql
  seeds.sql

vendor/           # Composer
.env              # nao versionar
```

## 7. Banco de dados (estrutura base)

### 7.1 Tabelas principais

- `users`
- `families`
- `family_members`
- `children`
- `people`
- `social_records`
- `referrals`
- `spiritual_followups`
- `delivery_events`
- `deliveries`
- `equipment`
- `equipment_loans`
- `visits`
- `audit_logs`

### 7.2 Relacionamentos principais

- `families` 1:N `family_members`
- `families` 1:N `children`
- `people` 1:N `social_records`
- `social_records` 1:N `referrals`
- `people` 1:N `spiritual_followups`
- `delivery_events` 1:N `deliveries`
- `equipment` 1:N `equipment_loans`
- `users` referencia criacao/operacao em varias tabelas

### 7.3 Regras de modelagem

- IDs `INT AUTO_INCREMENT` para simplicidade (Hostinger)
- Foreign keys com `InnoDB`
- Charset `utf8mb4`
- Timestamps em tabelas operacionais
- Indices para filtros frequentes (CPF, nome, data, status)

## 8. Regras de negocio essenciais

1. CPF unico em `families` e `people` quando informado.
2. Permitir cadastro parcial (CPF/RG nulo) para situacao de rua.
3. Bloqueio mensal de cesta por familia quando a regra do evento estiver ativa.
4. Senha (`ticket_number`) sequencial por evento e imutavel apos publicacao.
5. Fluxo de entrega: `nao_veio -> presente -> retirou`.
6. Ao marcar `retirou`, gravar data/hora, usuario e assinatura simples.
7. Emprestimo altera status do equipamento para `emprestado`.
8. Devolucao altera status para `disponivel` ou `manutencao`.
9. Alertas automaticos para pendencias, visitas e atrasos.
10. Ficha social exige consentimento (versao, nome e data/hora).

## 9. Relatorios e exportacoes (alvo)

- Familias atendidas no periodo
- Cestas por evento e por periodo
- Criancas atendidas (com familia/evento)
- Encaminhamentos por tipo/status
- Equipamentos (emprestados, devolvidos, atrasados, manutencao)
- Pendencias (documentacao e visitas)

Formatos:

- PDF

## 10. Regras de deploy (Hostinger)

- Configurar banco MySQL/MariaDB dedicado
- Criar `.env` com `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `APP_URL`, `APP_ENV`, `APP_KEY`
- Apontar document root para `public/`
- Ativar HTTPS e forcar redirecionamento
- Configurar rotina de backup/exportacao

## 11. Roadmap em sprints (1 a 20)

### Sprint 1 - Fundacao do projeto

- Criar estrutura de pastas MVC
- Configurar Composer
- Configurar bootstrap da aplicacao (`public/index.php`)
- Configurar `.env` e conexao PDO
- Criar roteador basico

### Sprint 2 - Autenticacao (base)

- Tela de login
- Sessao autenticada
- Logout
- Middleware de autenticacao
- Hash de senha e seed admin inicial

### Sprint 3 - Usuarios e RBAC

- CRUD de usuarios (admin)
- Perfis (`admin`, `voluntario`, `pastoral`, `viewer`)
- Middleware de permissao por rota/acao
- Ativar/desativar usuario

### Sprint 4 - Layout e dashboard inicial

- Layout base (header/sidebar/footer)
- Dashboard com cards vazios/placeholder
- Navegacao entre modulos
- Padrao visual responsivo com Bootstrap

### Sprint 5 - Banco de dados (schema MVP)

- Criar `database/schema.sql` com tabelas principais
- Criar migrations simples (ou scripts versionados)
- Popular `seeds.sql` com dados iniciais
- Validar chaves estrangeiras e indices basicos

### Sprint 6 - Modulo Familias (cadastro base)

- Lista de familias com busca
- Criar/editar familia
- Validacao de CPF e duplicidade
- Campos de endereco e dados socioeconomicos principais

### Sprint 7 - Familias (detalhe + membros)

- Tela detalhe da familia
- CRUD de `family_members`
- Calculo de renda familiar total
- Indicadores (adultos, trabalhadores, renda)

### Sprint 8 - Criancas

- CRUD de `children` vinculado a familia
- Lista de criancas com filtros simples
- Exibir criancas na aba da familia

### Sprint 9 - Pessoas acompanhadas (cadastro base)

- Lista de pessoas acompanhadas
- Cadastro/edicao de `people`
- Suporte a situacao de rua (campos especificos)
- Permitir dados incompletos quando necessario

### Sprint 10 - Ficha social (atendimento)

- Cadastro de `social_records`
- Consentimento digital obrigatorio
- Vinculo opcional com familia
- Linha do tempo basica por pessoa

### Sprint 11 - Encaminhamentos e acompanhamento espiritual

- CRUD de `referrals` por atendimento
- CRUD de `spiritual_followups`
- Filtros por tipo/status
- Historico no detalhe da pessoa

### Sprint 12 - Eventos de entrega (cestas)

- CRUD de `delivery_events`
- Regras do evento (bloqueio mensal, limite de cestas)
- Status do evento (rascunho/aberto/concluido)

### Sprint 13 - Entregas (lista operacional)

- Geracao de convidados/manual
- Registro em `deliveries`
- Senha sequencial por evento
- Fluxo de status (`nao_veio`, `presente`, `retirou`)
- Assinatura simples (nome digitado)

### Sprint 14 - Regras de bloqueio e validacoes de entrega

- Bloqueio de multiplas entregas no mesmo mes
- Validacoes por familia/pessoa
- Controle de quantidade de cestas
- Logs de operacao de entrega

### Sprint 15 - Equipamentos (cadastro e estoque)

- CRUD de `equipment`
- Codigo automatico por tipo
- Status e estado de conservacao
- Filtros por tipo/status/codigo

### Sprint 16 - Emprestimos e devolucoes

- Fluxo de emprestimo (`equipment_loans`)
- Fluxo de devolucao
- Atualizacao automatica do status do equipamento
- Alertas de devolucao atrasada

### Sprint 17 - Visitas e pendencias

- CRUD de `visits`
- Solicitar/concluir visita
- Alertas de pendencias (docs/visitas/atualizacao)
- Parametro de tempo sem atualizacao

### Sprint 18 - Relatorios e exportacoes

- Relatorios mensais (familias, cestas, criancas, encaminhamentos)
- Exportacao PDF
- Filtros por periodo/status/bairro

### Sprint 19 - Auditoria, seguranca e hardening

- `audit_logs` para eventos relevantes
- Bloqueio por tentativas de login
- Recuperacao de senha (token)
- Revisao de permissoes por modulo
- Revisao de sessao/cookies/headers

### Sprint 20 - Deploy e fechamento MVP

- Ajustes finais de UX
- Teste integrado dos fluxos principais
- Script final de banco e seed
- Checklist de deploy Hostinger
- Publicacao inicial e documentacao operacional

## 12. Definicao de pronto (DoD) por sprint

- Funcionalidade implementada e navegavel
- Validacoes principais funcionando
- Sem erro fatal em fluxo principal
- Registro em banco consistente
- Permissao minima aplicada quando necessario
- Documentacao curta da sprint atualizada

## 13. Proximos artefatos recomendados (apos este plano)

- `database/schema.sql` (DDL inicial)
- `docs/regras-negocio.md`
- `docs/telas-e-fluxos.md`
- `docs/backlog.md`
- `docs/checklist-deploy-hostinger.md`

