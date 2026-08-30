# Relatório de correções — Famílias e lista operacional

**Projeto:** Dashboard PHP PBT / Igreja Social

**Data:** 30/08/2026

**Branch:** `codex/correcoes-familias-entregas-2026-08-30`

**Ambiente:** local, `http://127.0.0.1:8000`

## 1. Escopo

Foram corrigidos os problemas de CPF na composição familiar, listagem incompleta de famílias e seleção limitada de famílias em Eventos de Entrega. O documento de solicitações da cliente foi usado como especificação e critério de aceite.

Não foram removidos registros nem alterados dados de produção. Os testes com escrita utilizaram exclusivamente a base local `dashboard_php_pbt_local` e fixtures temporárias.

## 2. Causas raiz

### CPF na composição familiar

O cadastro geral já tratava `cpf_responsible` como opcional, mas a tela de composição familiar mantinha `required` no HTML e `FamilyCompositionService::validatePrincipalInput()` rejeitava CPF vazio no backend. Isso criava divergência entre os dois fluxos.

### Listagem de famílias

`FamilyModel::search()` aplicava `LIMIT 200` depois da ordenação, sem paginação. A contagem total era feita por outra consulta, permitindo que a tela informasse uma quantidade maior do que a quantidade realmente disponível para navegação.

### Lista operacional de Entregas

`DeliveryEventController::show()` reutilizava `FamilyModel::search(['status' => 'ativo'])`. O mesmo limite fixo de 200 truncava o conjunto de famílias elegíveis do dropdown. O campo também era um `<select>` sem pesquisa por nome ou CPF.

## 3. Correções realizadas

- CPF do responsável principal agora é opcional na composição; CPF informado continua sujeito a validação e duplicidade.
- O limite fixo de 200 foi removido da busca de famílias, mantendo os filtros e a ordenação existentes.
- O seletor de famílias de Entregas agora pesquisa localmente por nome e CPF, mantendo o envio de `family_id` e o filtro de famílias ativas.
- O smoke test foi ajustado para aceitar a URL local por parâmetro e cobrir renda sem CPF, CPF válido, CPF inválido e presença do seletor pesquisável.
- Foi criado teste transacional com 205 famílias para impedir regressão do truncamento.
- Os logos do cabeçalho foram ampliados para melhorar a leitura em telas estreitas.
- O rótulo redundante do cabeçalho móvel e os textos promocionais do rodapé foram removidos; o rodapé agora identifica a instituição como “Dashboard Primeira Igreja Batista”.
- A tela de login passou a exibir somente o logo principal, ampliado e centralizado no card.
- O rótulo “Igreja Social” foi removido da tela de login e o subtítulo foi atualizado para “Dashboard Primeira Igreja Batista”.

## 4. Arquivos modificados

| Arquivo | Alteração |
|---|---|
| `app/Services/FamilyCompositionService.php` | CPF opcional na validação do responsável principal |
| `app/Views/families/show.php` | CPF do responsável identificado como opcional |
| `app/Models/FamilyModel.php` | Remoção do limite fixo da busca |
| `app/Views/delivery_events/show.php` | Campo de pesquisa por nome/CPF no seletor |
| `public/assets/family-form-enhancements.js` | Filtro client-side das opções do seletor |
| `.local/smoke_test_e2e.ps1` | Regressões funcionais e URL parametrizável |
| `.local/test_family_listing.php` | Teste transacional de listagem acima de 200 registros |
| `app/Views/auth/login.php` | Mantém apenas o logo principal, centraliza-o e atualiza a identidade textual |
| `app/Views/layouts/app.php` | Remove rótulo móvel e textos antigos do rodapé |
| `public/assets/app.css` | Amplia os logos do cabeçalho e do login |
| `docs/RELATORIO_CORRECOES_FAMILIAS_ENTREGAS_2026-08-30.md` | Registro técnico desta entrega |

## 5. Banco e preservação de dados

Foi criado backup local antes dos testes com escrita:

`C:\Users\Administrador\AppData\Local\Temp\dashboard_php_pbt_local_backup_20260830.json`

O backup foi gerado via PDO porque `mysqldump` não estava disponível no `PATH`. Ele contém a estrutura e os dados das 17 tabelas da base local.

Não foi necessária migration. A coluna `families.cpf_responsible` já aceita `NULL`, e as correções não alteram o schema. Não foram modificadas regras de bloqueio mensal, limite de cestas, senhas, presença, retirada, permissões ou exceções autorizadas.

## 6. Validações

- `php .local\test_family_listing.php`: passou com 205 fixtures transacionais e rollback automático.
- `.local\smoke_test_e2e.ps1 -BaseUrl http://127.0.0.1:8000`: aprovado, incluindo login, CRUDs, renda sem CPF, CPF válido/inválido e Entregas.
- Fluxo HTTP integrado: famílias A/M/Z adicionadas, senhas geradas, presença e retirada confirmadas, limite de cestas respeitado e bloqueio mensal confirmado.
- Lint PHP nos arquivos alterados: sem erros.
- `/health`: HTTP 200 com `status: ok`.
- Navegador real: busca por nome e CPF filtrou corretamente as opções do seletor.
- Navegador real: tela de login validada em 770×740 e 390×844, com apenas um logo, sem overflow horizontal e sem erros de console; o link de recuperação de senha continuou navegando para `/forgot-password`.
- Navegador real: logo do login validado com 64×64 px e centralização exata dentro do card; “Igreja Social” ausente e subtítulo institucional atualizado.
- `git diff --check`: executado antes dos commits funcionais.

O navegador ainda registra dois avisos preexistentes fora deste escopo: a fonte Google é bloqueada pela CSP atual e `favicon.ico` retorna 404. Não houve erro JavaScript introduzido pelo seletor.

## 7. Commits

- `fix(banco): alinha schema consolidado às migrações`
- `test(smoke): atualiza dados obrigatórios de empréstimo`
- `docs(levantamento): registra ambiente local e validações`
- `fix(composicao): torna CPF do responsavel opcional`
- `fix(familias): remove limite fixo da listagem`
- `fix(entregas): permite pesquisar todas as familias elegiveis`
- `docs(correcoes): registra causa raiz e evidencias`
- `fix(layout): ajusta logos e textos institucionais`
- `docs(layout): registra ajustes visuais do cabeçalho e login`
- `fix(login): ajusta identidade visual da tela`
- `docs(login): registra ajustes de identidade visual`

O push foi realizado após a conclusão das validações finais.
