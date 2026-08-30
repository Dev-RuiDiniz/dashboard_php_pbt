# Relatório de Levantamento Local

**Projeto:** Dashboard PHP PBT / Igreja Social  
**Data:** 30/08/2026  
**Escopo:** análise estrutural, instalação, banco local, execução HTTP e smoke test funcional.

## 1. Visão geral

O projeto é uma aplicação web server-side para operação social de igreja. O backend usa PHP com MVC customizado, PDO e MySQL/MariaDB. As telas são renderizadas por PHP; não existe pipeline Node/frontend separado.

Módulos identificados:

- autenticação, logout, bloqueio de tentativas e recuperação de senha;
- dashboard operacional;
- famílias, composição familiar e crianças;
- pessoas acompanhadas, fichas sociais, encaminhamentos e acompanhamento espiritual;
- eventos e entregas de cestas;
- equipamentos e empréstimos/devoluções;
- visitas;
- relatórios PDF/CSV/Excel;
- usuários e permissões administrativas;
- consulta de CEP.

## 2. Inventário técnico

| Item | Estado encontrado |
|---|---|
| PHP | 8.2.31 |
| Banco | MariaDB ativo na porta 3306 |
| PDO | `pdo_mysql` ativo |
| Extensões principais | `mbstring`, `openssl`, `json`, `dom`, `mysqli` ativas |
| Composer | `composer.phar` local, versão 2.9.5 |
| Dependências | Instaladas em `vendor/` |
| Controllers | 14 |
| Models | 15 |
| Services | 10 |
| Views PHP | 28 |
| Rotas GET/POST | 94 |
| Build Node | Não utilizado |

Estrutura principal:

- `public/`: front controller, `.htaccess` e assets;
- `app/Controllers`: entrada dos fluxos HTTP;
- `app/Models`: acesso a dados via PDO;
- `app/Services`: regras de negócio e integrações;
- `app/Views`: layout e telas server-side;
- `config/`: bootstrap de rotas, aplicação e banco;
- `database/`: schema consolidado, seeds e migrations;
- `storage/`: uploads, logs e exports;
- `docs/`: operação, deploy, manual e histórico do projeto.

## 3. Rotas e telas

As rotas estão concentradas em `config/routes.php`.

| Área | Rotas principais | Status verificado |
|---|---|---|
| Entrada | `/`, `/health`, `/login` | OK |
| Recuperação | `/forgot-password`, `/reset-password` | Implementada; fluxo não destrutivo não foi executado integralmente |
| Dashboard | `/dashboard` | OK autenticado |
| Famílias | `/families`, `/families/show`, `/families/create`, `/families/edit` | OK autenticado |
| Crianças | `/children` e ações compatíveis | Integrada ao detalhe de família |
| Pessoas | `/people`, `/people/show`, `/people/create`, `/people/edit` | OK autenticado como admin |
| Fichas sociais | `/social-records` e ações dentro de pessoa | CRUD exercitado no smoke test |
| Entregas | `/delivery-events`, detalhe, lista operacional, CSV e impressão | CRUD exercitado no smoke test |
| Equipamentos | `/equipment` e CRUD | CRUD exercitado no smoke test |
| Empréstimos | `/equipment-loans`, devolução, manutenção e exclusão | Inclusão/exclusão exercitada no smoke test |
| Visitas | `/visits`, criação, edição e conclusão | Tela autenticada respondeu 200; CRUD completo ainda requer teste dedicado |
| Relatórios | `/reports`, PDF, CSV e Excel/XLSX | Tela autenticada respondeu 200; exports ainda requerem teste dedicado |
| Usuários | `/users`, criação, edição, ativação e exclusão | Criação/exclusão exercitadas no smoke test |
| CEP | `/api/cep` | Protegida por autenticação; integração externa depende de rede/configuração |

## 4. Banco de dados

A base local isolada utilizada para correções é `dashboard_php_pbt_local`, configurada no `.env` ignorado pelo Git.

Estado confirmado:

- 17 tabelas;
- 26 chaves estrangeiras;
- 15 registros em `schema_migrations`;
- 4 usuários seed, um por perfil;
- dados seed de famílias, pessoas e equipamentos;
- tabelas principais: `users`, `families`, `family_members`, `family_phones`, `children`, `people`, `person_phones`, `social_records`, `referrals`, `spiritual_followups`, `delivery_events`, `deliveries`, `equipment`, `equipment_loans`, `visits` e `audit_logs`.

## 5. Problemas encontrados e corrigidos

### Baseline SQL incompleto — corrigido

`database/final_mvp.sql` já era usado como schema consolidado, mas não continha:

- `children.income`, exigido pelo `ChildModel` e pela migration 015;
- os campos de exceção mensal em `deliveries`, exigidos pela migration 014 e pelo fluxo de entregas;
- a chave estrangeira e o índice da exceção mensal;
- os registros de migrations 012–015 em `schema_migrations`.

Isso fazia uma base criada pelo próprio `final_mvp.sql` falhar ao abrir o detalhe de evento ou ao executar migrations. O baseline foi alinhado ao código e às migrations.

### Smoke test desatualizado — corrigido

`.local/smoke_test_e2e.ps1` criava empréstimo sem enviar os campos que o controller tornou obrigatórios: responsável, telefone, CPF, endereço e usuário do equipamento. O teste foi atualizado para refletir o contrato atual.

## 6. Segurança

Pontos positivos identificados:

- autenticação por sessão;
- regeneração de sessão após login/logout;
- cookies `HttpOnly` e `SameSite=Strict`;
- senhas com `password_hash`/bcrypt;
- queries de models com prepared statements;
- RBAC por middleware;
- headers `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy` e CSP;
- bloqueio de tentativas inválidas de login;
- token de reset armazenado por hash e com expiração;
- auditoria de operações sensíveis em `audit_logs`.

Riscos e pendências:

| Severidade | Item | Impacto |
|---|---|---|
| Alta | Não foram encontrados tokens CSRF nos formulários POST nem validação CSRF nos controllers | Usuário autenticado pode ser induzido a executar ações por uma página externa |
| Alta | Usuários seed usam senha conhecida (`admin123`) | Risco imediato se a base for publicada sem troca das credenciais |
| Média | CI valida Composer e lint, mas não executa smoke/integrado nem banco | Regressões de schema e fluxos podem chegar ao branch principal |
| Média | `APP_DEBUG` e token de recuperação exposto em tela são adequados apenas para ambiente local | Configuração incorreta em produção pode expor dados operacionais |
| Baixa | Documentação antiga cita mPDF, enquanto o código usa Dompdf | Divergência operacional/documental |

## 7. Integrações externas

- Correios CEP: configurável por `CEP_CORREIOS_BASE_URL` e bearer token;
- ViaCEP: fallback configurável por `CEP_ENABLE_VIACEP_FALLBACK`;
- Bootstrap via CDN jsDelivr;
- fontes Google em uma view pública;
- Dompdf para geração de PDF.

As integrações de CEP dependem de rede e, para Correios, de token válido. O funcionamento essencial do sistema não depende delas para login e CRUDs.

## 8. Instalação e execução realizadas

Comandos executados:

```powershell
php .local\tools\composer.phar install --no-interaction --prefer-dist
php .local\tools\composer.phar dump-autoload --no-interaction --optimize
php database\migrate.php
php -S 127.0.0.1:8000 -t public
```

O Composer instalou 6 pacotes e gerou autoload otimizado com 249 classes. A ausência de ZIP/7-Zip apenas fez o Composer usar o cache/source; a instalação terminou com código 0.

## 9. Validações executadas

- lint PHP em 81 arquivos: 0 falhas;
- `/health`: HTTP 200 e JSON `status: ok`;
- `/login`: HTTP 200;
- rotas protegidas sem sessão: redirecionamento HTTP 302;
- 12 telas principais autenticadas: todas HTTP 200;
- smoke E2E: `RESULTADO_FINAL: APROVADO`;
- migrations após o ajuste do baseline: todas as 15 registradas e execução sem erro;
- banco local: schema, foreign keys e tabelas principais confirmados.

## 10. Como acessar localmente

Servidor principal mantido em:

`http://127.0.0.1:8000`

Credencial seed local para teste:

- e-mail: `admin@igrejasocial.local`;
- senha: `admin123`.

Banco usado pelo `.env` local:

- host: `127.0.0.1`;
- porta: `3306`;
- base: `dashboard_php_pbt_local`;
- usuário: `root`;
- senha: vazia, somente no ambiente local atual.

## 11. Próximas correções recomendadas

1. Implementar proteção CSRF para login, CRUDs, exclusões, mudanças de status e operações administrativas.
2. Criar testes automatizados para migrations, autorização, reset de senha e regras de entrega/devolução.
3. Adicionar smoke test ao CI com MariaDB temporário.
4. Testar individualmente exports PDF/CSV/Excel e o fluxo completo de visitas.
5. Atualizar documentação que ainda cita mPDF e revisar checklist de produção.
6. Garantir troca obrigatória das credenciais seed antes de qualquer publicação.

## Parecer

O sistema está executável localmente e com os principais fluxos operacionais disponíveis para correção. O ambiente local foi isolado da base existente e o servidor está acessível em `http://127.0.0.1:8000`. O maior risco técnico pendente é a ausência aparente de proteção CSRF nos formulários POST; o maior risco operacional é publicar as senhas seed sem substituí-las.
