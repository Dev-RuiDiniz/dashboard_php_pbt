# Relatório Completo de Mudanças

Projeto: Dashboard PHP PBT / Igreja Social  
Período auditado: 13/03/2026 a 16/03/2026  
Base da análise: histórico Git validado localmente no repositório  
Responsável pelos commits: Dev-RuiDiniz  
Data de geração deste relatório: 16/03/2026

## 1. Resumo executivo

Entre 13/03/2026 e 16/03/2026 o projeto recebeu uma frente extensa de evolução no módulo de famílias, com foco em produtividade operacional, centralização do cadastro, automação de indicadores, consistência documental e endurecimento de regras de segurança. No total, foram confirmados 32 commits no período.

O trabalho concentrou-se principalmente em transformar o detalhe da família no centro da operação: a composição familiar passou a ser gerida por abas, o cadastro de principal, membros e crianças foi unificado, os cálculos de idade e renda ficaram automáticos, as validações de CPF e documentos foram reforçadas e o histórico operacional da família passou a exibir entregas, empréstimos, visitas e pendências no mesmo contexto.

Além da frente funcional, houve refatoração estrutural relevante em 15/03/2026 com extração de serviços dedicados para composição, cadastro, detalhe e indicadores da família. Em 16/03/2026, o escopo foi finalizado com correções de navegação entre abas, suporte a sobrescritas locais de ambiente, restrição de exclusão de famílias a administradores e alinhamento do `final_mvp.sql` com a linha base de migrations.

## 2. Métricas consolidadas

| Indicador | Resultado |
| --- | --- |
| Total de commits auditados | 32 |
| Dias com mudanças confirmadas | 3 dias úteis de entrega (13, 15 e 16/03/2026) |
| Arquivos únicos impactados | 45 |
| Volume bruto somado dos commits | 4.400 inserções / 1.756 remoções |
| Arquivos mais tocados | `app/Controllers/FamilyController.php`, `app/Views/families/show.php`, `app/Views/families/form.php`, `public/assets/family-form-enhancements.js`, `app/Models/FamilyModel.php` |
| Áreas mais impactadas | `app/`, `docs/`, `database/`, `public/`, `config/` |

## 3. Resultado final entregue ao cliente

- O módulo de famílias passou a operar com abas de detalhe, permitindo concentrar cadastro, consulta e acompanhamento operacional em um único fluxo.
- O cadastro da composição familiar foi consolidado: o estado final entregue no sistema usa `principal`, `membro` e `criança`, sem fluxo separado de `dependente`.
- O sistema passou a calcular automaticamente idade da responsável, idade das crianças, renda familiar total, média per capita, total de adultos, total de trabalhadores e total de crianças.
- Documentos foram ampliados no banco e na interface, com CPF único global e validações mais consistentes para família e pessoas acompanhadas.
- O histórico da família foi enriquecido com acessos por abas para resumo, entregas, empréstimos, visitas/anotações e pendências.
- A exclusão de famílias ficou restrita a administradores, com checagem adicional de confirmação no servidor.
- O pacote SQL final foi sincronizado com a realidade das migrations para facilitar instalação limpa e manutenção incremental sem reaplicar estrutura já consolidada.
- Manual do cliente, roteiros de teste, registros de sprint e PDF operacional foram atualizados ao longo da entrega.

## 4. Linha do tempo detalhada por data

## 4.1. Commits de 16/03/2026

### 14:19 - `c8daec2` - Align final MVP SQL with migration baseline

- Alinhou `database/final_mvp.sql` com a base já consolidada até a Sprint 37.
- Criou e populou `schema_migrations` dentro do SQL final para evitar reaplicação indevida de migrations em bases novas.
- Atualizou `README.md` e `database/migrations/README.md` para separar claramente o fluxo de banco novo e o fluxo de banco existente.
- Ajustou a nomenclatura de `database/schema.sql` para refletir o estado consolidado do schema.
- Impacto final: instalação inicial e atualização incremental ficaram mais previsíveis para deploy e suporte.

Arquivos principais: `database/final_mvp.sql`, `database/schema.sql`, `database/migrations/README.md`, `README.md`

### 13:57 - `b60e2e6` - Restrict family deletion to admins

- Protegeu a rota `POST /families/delete` com permissão exclusiva de administrador.
- Exigiu confirmação explícita (`confirm_delete`) também no servidor, reduzindo risco de exclusão acidental.
- Ocultou ações de exclusão para perfis sem permissão nas telas de lista e detalhe da família.
- Manteve confirmação visual no navegador para reforçar a ação destrutiva.
- Impacto final: endurecimento de segurança e redução de risco operacional.

Arquivos principais: `config/routes.php`, `app/Controllers/FamilyController.php`, `app/Views/families/index.php`, `app/Views/families/show.php`

### 13:06 - `4e2d207` - Unify family members and remove dependent flow

- Removeu o tipo separado de `dependente` da interface de composição familiar.
- Simplificou a lógica de controladores, serviços, views e JavaScript para tratar todos os parentes adultos no fluxo de `membro`.
- Atualizou cópias, mensagens e validações para refletir a nova terminologia final.
- Consolidou o estado funcional entregue após a refatoração do dia 15/03/2026.
- Impacto final: menos complexidade de uso, menos duplicação de regras e menor ambiguidade no cadastro da família.

Arquivos principais: `app/Controllers/FamilyController.php`, `app/Controllers/PersonController.php`, `app/Services/FamilyCompositionService.php`, `app/Services/FamilyDetailService.php`, `app/Views/families/form.php`, `app/Views/families/show.php`, `public/assets/family-form-enhancements.js`

### 12:49 - `a24fde0` - Fix family detail tabs and support local env overrides

- Corrigiu o comportamento das abas do detalhe da família para não forçar retorno indevido à aba de composição.
- Permitiu abrir corretamente as abas de resumo, entregas, empréstimos, visitas e pendências.
- Passou a carregar `.env.local` após `.env`, permitindo sobrescritas locais sem alterar o arquivo versionado.
- Incluiu `.env.local` no `.gitignore`.
- Impacto final: navegação funcional nas abas e melhor experiência de desenvolvimento/local troubleshooting.

Arquivos principais: `.gitignore`, `app/Core/Env.php`, `app/Services/FamilyDetailService.php`

## 4.2. Commit de 15/03/2026

### 11:43 - `1ea3afe` - Refatora familias com abas e composicao unificada

- Realizou a principal refatoração estrutural do período no módulo de famílias.
- Extraiu regras do `FamilyController` para serviços dedicados: `FamilyCompositionService`, `FamilyRegistrationService`, `FamilyDetailService`, `FamilyIndicatorsService` e `FamilyDataSupport`.
- Reorganizou o detalhe da família com abas para composição familiar, resumo, entregas, empréstimos, visitas/anotações e pendências.
- Adicionou histórico contextual por `family_id` para entregas, empréstimos e visitas, com atalhos de pré-seleção.
- Incluiu campos e regras ligadas a `responsible_works`, `responsible_income` e `family_income_average`.
- Ajustou `database/final_mvp.sql`, `database/schema.sql`, `database/seeds.sql` e adicionou `database/migrations/006_family_income_average_and_principal_work.sql`.
- Atualizou documentação operacional, checklist e roteiro de testes integrados.
- O próprio commit registra validação executada com `php -l` completo e smoke test de `/health` e `/login`.
- Observação importante de escopo: esta refatoração reintroduziu temporariamente o fluxo explícito de `dependente`, mas esse desenho foi simplificado no dia 16/03/2026 pelo commit `4e2d207`.

Arquivos principais: `app/Controllers/FamilyController.php`, `app/Services/FamilyCompositionService.php`, `app/Services/FamilyRegistrationService.php`, `app/Services/FamilyDetailService.php`, `app/Services/FamilyIndicatorsService.php`, `app/Services/FamilyDataSupport.php`, `app/Views/families/show.php`, `database/migrations/006_family_income_average_and_principal_work.sql`, `docs/sprints/sprint-37.md`

## 4.3. Commits de 13/03/2026

### 17:55 - `2f38e36` - docs: add local codex skill reference

- Registrou no repositório a referência local da skill do Codex usada para manutenção do projeto, incluindo `README.md` e `docs/CODEX_LOCAL_SKILL.md`.

Arquivos principais: `README.md`, `docs/CODEX_LOCAL_SKILL.md`

### 16:22 - `ff5135a` - refactor(families): consolidar membro e dependente em aba unica

- Consolidou, naquele estágio da sprint, os fluxos de membro e dependente em uma única aba de composição, reduzindo duplicidade de interface e regras.

Arquivos principais: `app/Controllers/FamilyController.php`, `app/Views/families/show.php`, `public/assets/family-form-enhancements.js`

### 16:15 - `487f531` - fix(families): corrigir cpf unico global e registrar validacao local

- Corrigiu a regra de CPF único global no contexto de famílias e documentou a validação local executada em `docs/VALIDACAO_LOCAL_2026-03-13.md`.

Arquivos principais: `app/Models/FamilyModel.php`, `docs/VALIDACAO_LOCAL_2026-03-13.md`, `docs/sprints/sprint-36.md`

### 15:50 - `dac2cbd` - fix(people): aplicar cpf unico global no cadastro de pessoas

- Estendeu para o módulo de pessoas acompanhadas a mesma política de CPF único global adotada no fluxo de famílias.

Arquivos principais: `app/Controllers/PersonController.php`

### 15:48 - `8ebc1f4` - docs(families): atualizar manual e roteiro de testes para automacoes e documentos

- Atualizou manual do cliente, PDF do manual, roteiro integrado e sprint para refletir automações de idade/renda e o novo tratamento documental.

Arquivos principais: `docs/MANUAL_CLIENTE.md`, `docs/MANUAL_CLIENTE.pdf`, `docs/TESTE_INTEGRADO_MVP.md`, `docs/sprints/sprint-36.md`

### 15:45 - `d77c78c` - feat(families): incluir documentos completos no cadastro de pessoas da familia

- Ampliou o cadastro de pessoas da família para capturar documentação completa, ajustando tela e comportamento JavaScript de apoio.

Arquivos principais: `app/Views/families/form.php`, `app/Views/families/show.php`, `public/assets/family-form-enhancements.js`

### 15:42 - `9983794` - feat(families): automatizar idade para principal membro e crianca

- Passou a calcular automaticamente a idade da responsável principal e das crianças com base na data de nascimento, reduzindo digitação e inconsistência manual.

Arquivos principais: `app/Controllers/FamilyController.php`, `app/Views/families/show.php`, `public/assets/family-form-enhancements.js`

### 15:39 - `f8600d8` - refactor(families): tornar renda e indicadores totalmente automaticos

- Removeu dependências de preenchimento manual de renda e indicadores, centralizando o cálculo automático na regra de negócio e na exibição do formulário.

Arquivos principais: `app/Controllers/FamilyController.php`, `app/Models/FamilyModel.php`, `app/Views/families/form.php`

### 15:37 - `4258f22` - feat(families): validar documentos por tipo e cpf unico global

- Introduziu validações documentais por tipo de pessoa e reforçou a unicidade global de CPF em famílias, membros e crianças.

Arquivos principais: `app/Controllers/ChildController.php`, `app/Controllers/FamilyController.php`, `app/Models/ChildModel.php`, `app/Models/FamilyModel.php`

### 15:30 - `5917be9` - feat(db): adicionar cpf e rg para membros e criancas

- Atualizou o schema e a migration `005_family_people_documents.sql` para armazenar CPF e RG também em membros e crianças.

Arquivos principais: `database/schema.sql`, `database/final_mvp.sql`, `database/migrations/005_family_people_documents.sql`, `database/migrations/README.md`

### 15:02 - `bf9ce0c` - fix(families): abrir detalhe apos criar para continuar cadastro na mesma aba

- Ajustou o fluxo pós-criação da família para abrir imediatamente o detalhe e permitir continuidade do cadastro na mesma jornada.

Arquivos principais: `app/Controllers/FamilyController.php`, `app/Views/families/form.php`

### 14:56 - `0a67aac` - docs(families): atualizar manual e testes para cadastro completo na aba familia

- Atualizou documentação operacional e PDF do manual para refletir o fluxo de cadastro completo pela aba da família.

Arquivos principais: `docs/MANUAL_CLIENTE.md`, `docs/MANUAL_CLIENTE.pdf`, `docs/TESTE_INTEGRADO_MVP.md`, `docs/sprints/sprint-35.md`

### 14:55 - `a248201` - feat(families): permitir cadastro de principal membro dependente e crianca na aba familia

- Expandiu o fluxo unificado da aba da família para suportar principal, membro, dependente e criança no mesmo contexto operacional daquele estágio da entrega.

Arquivos principais: `app/Controllers/FamilyController.php`, `app/Models/FamilyModel.php`, `app/Views/families/show.php`, `config/routes.php`, `public/assets/family-form-enhancements.js`

### 14:35 - `9f07182` - docs: regenerar pdf do manual do cliente

- Regenerou o artefato PDF do manual após a rodada de mudanças funcionais e documentais do módulo de famílias.

Arquivos principais: `docs/MANUAL_CLIENTE.pdf`

### 14:34 - `2dad7f8` - docs(families): documentar fluxo unificado de cadastro na aba familia

- Documentou no `README`, manual, testes e sprint o novo fluxo unificado de cadastro na aba da família.

Arquivos principais: `README.md`, `docs/MANUAL_CLIENTE.md`, `docs/TESTE_INTEGRADO_MVP.md`, `docs/sprints/sprint-34.md`

### 14:34 - `dc16754` - feat(families): unificar cadastro de membro dependente e crianca na aba familia

- Consolidou em uma única experiência de tela o cadastro de membro, dependente e criança dentro do detalhe da família.

Arquivos principais: `app/Controllers/FamilyController.php`, `app/Views/families/show.php`, `public/assets/family-form-enhancements.js`

### 14:11 - `91983e9` - docs: registrar consolidacao de criancas no fluxo de familias

- Atualizou documentação e sprint para registrar a centralização das crianças dentro do fluxo de famílias.

Arquivos principais: `README.md`, `docs/MANUAL_CLIENTE.md`, `docs/README.md`, `docs/TESTE_INTEGRADO_MVP.md`, `docs/sprints/sprint-33.md`

### 14:11 - `8fb7047` - chore(db): sincronizar children_count com base na tabela children

- Criou a migration `004_children_count_backfill.sql` para recompor `children_count` a partir da tabela `children`.

Arquivos principais: `database/migrations/004_children_count_backfill.sql`, `database/migrations/README.md`

### 14:11 - `27b48b1` - feat(families): centralizar cadastro de criancas na aba da familia

- Moveu o cadastro de crianças para a aba de detalhe da família e ajustou rotas, permissões, controller e view para esse novo centro de operação.

Arquivos principais: `app/Controllers/ChildController.php`, `app/Controllers/FamilyController.php`, `app/Models/FamilyModel.php`, `app/Services/AuthorizationService.php`, `app/Views/families/show.php`, `config/routes.php`

### 13:23 - `6b96efe` - docs: atualizar manual e registros da sprint de cadastro familia

- Atualizou documentação, README interno de docs e registro de sprint para acompanhar a rodada inicial de mudanças no cadastro familiar.

Arquivos principais: `docs/MANUAL_CLIENTE.md`, `docs/README.md`, `docs/TESTE_INTEGRADO_MVP.md`, `docs/sprints/sprint-32.md`

### 13:21 - `5163a96` - refactor(families): simplificar fluxo de digitacao para entrevistas

- Ajustou o formulário de famílias para reduzir atrito de preenchimento e simplificar a digitação durante entrevistas e atendimento.

Arquivos principais: `app/Views/families/form.php`

### 13:20 - `a0e685f` - fix(families): normalizar validacoes de entrada do cadastro

- Reforçou sanitização, obrigatoriedades e validações de entrada no `FamilyController`, padronizando o comportamento do cadastro.

Arquivos principais: `app/Controllers/FamilyController.php`

### 13:19 - `92c1474` - feat(families): destacar numero e total de familias

- Passou a destacar número/total de famílias e consolidou apoio visual e de consulta com ajustes em controller, model e telas de lista, formulário e detalhe.

Arquivos principais: `app/Controllers/FamilyController.php`, `app/Models/FamilyModel.php`, `app/Views/families/form.php`, `app/Views/families/index.php`, `app/Views/families/show.php`

### 13:17 - `19ee764` - feat(families): exibir idade calculada da responsavel

- Exibiu automaticamente a idade calculada da responsável no formulário e no comportamento assistido por JavaScript.

Arquivos principais: `app/Views/families/form.php`, `public/assets/family-form-enhancements.js`

### 13:16 - `b92a026` - feat(families): padronizar listas controladas no formulario

- Padronizou listas controladas do formulário de famílias, reduzindo variações livres de preenchimento e melhorando consistência dos dados.

Arquivos principais: `app/Controllers/FamilyController.php`, `app/Views/families/form.php`

### 13:15 - `d685922` - feat(families): aplicar mascaras de cpf rg telefone no cadastro

- Adicionou máscaras de CPF, RG e telefone no formulário de famílias por meio do script `family-form-enhancements.js`.

Arquivos principais: `app/Views/layouts/app.php`, `public/assets/family-form-enhancements.js`

### 13:14 - `9d484f8` - chore(families): alinhar requisitos operacionais de cadastro rapido

- Registrou checklist operacional em `docs/FAMILIES_WORD_CHECKLIST.md` para orientar o cadastro rápido e a conversão das necessidades de atendimento em requisitos concretos.

Arquivos principais: `docs/FAMILIES_WORD_CHECKLIST.md`

## 5. Síntese por tema

### 5.1. Evolução funcional do módulo de famílias

- O detalhe da família deixou de ser apenas uma tela de consulta e passou a ser o hub principal de trabalho.
- Crianças foram centralizadas no fluxo da família.
- Principal, membros e crianças passaram a ser mantidos de forma unificada no contexto da composição familiar.
- O estado final entregue removeu o fluxo separado de dependente, simplificando operação e treinamento.

### 5.2. Qualidade e consistência de dados

- CPF e RG foram estendidos para membros e crianças no banco.
- CPF único global passou a valer tanto no módulo de famílias quanto no módulo de pessoas.
- Listas controladas, máscaras e validações reduziram preenchimento inconsistente.
- Idade, renda total e média per capita passaram a depender menos de digitação manual.

### 5.3. Arquitetura e manutenção

- O `FamilyController` foi significativamente enxugado com extração de serviços.
- O detalhe da família ganhou composição modular e mais previsível para manutenção futura.
- O carregamento de `.env.local` melhorou o suporte a ambientes locais sem contaminar arquivos versionados.

### 5.4. Segurança e governança

- Exclusão de família ficou restrita a administradores.
- O servidor passou a exigir confirmação explícita antes de deletar.
- O SQL final ficou compatível com a tabela de migrations, reduzindo risco de retrabalho em deploy e atualização.

### 5.5. Documentação e operação

- Manual do cliente foi atualizado em múltiplas etapas.
- O PDF do manual foi regenerado sempre que o fluxo funcional mudou.
- Testes integrados, sprints e registros de validação local acompanharam a evolução do código.

## 6. Estado final percebido no sistema após os commits

Ao final do período auditado, o sistema entrega um módulo de famílias mais maduro, com jornada mais curta para cadastro, menos campos redundantes, melhor visibilidade dos indicadores e menos risco operacional. O atendimento passa a acontecer com mais fluidez porque o usuário consegue criar a família, cair no detalhe, completar composição e consultar histórico relacionado sem trocar de contexto.

Do ponto de vista técnico, o módulo também ficou mais sustentável: regras críticas foram organizadas em serviços específicos, a base SQL final foi reconciliada com migrations e o comportamento final do produto ficou mais coerente do que no início da sprint, especialmente pela simplificação do conceito de dependente.

## 7. Validação usada para este relatório

- Conferência do histórico Git local entre 13/03/2026 e 16/03/2026.
- Leitura de `git log`, `git show` e estatísticas dos commits confirmados.
- Verificação do diff consolidado e dos arquivos de documentação e banco afetados.
- Este relatório não executa novamente os testes do sistema; ele documenta as mudanças efetivamente registradas nos commits e, quando aplicável, reproduz validações já declaradas nos próprios commits e documentos.
