# Sprint 33 - Cadastro de criancas centralizado em Familias

## Entregas
- Centralizacao do cadastro de criancas/dependentes na aba da familia:
  - inclusao de formulario inline de crianca em `families/show`
  - edicao e exclusao de crianca na mesma pagina de detalhe da familia
- Desativacao do modulo direto de criancas:
  - menu `Criancas` removido do layout
  - rotas `/children*` mantidas apenas para compatibilidade com redirecionamento informativo
- Novas rotas de criancas sob contexto de familia:
  - `POST /families/children`
  - `POST /families/children/update`
  - `POST /families/children/delete`
- Consistencia automatica de `children_count`:
  - recalculo em operacoes de familia/membros/criancas
  - migracao de backfill para sincronizar base existente

## Entregaveis tecnicos
- Backend:
  - `app/Controllers/FamilyController.php`
  - `app/Controllers/ChildController.php`
  - `app/Models/FamilyModel.php`
  - `config/routes.php`
  - `app/Services/AuthorizationService.php`
- Interface:
  - `app/Views/families/show.php`
  - `app/Views/families/form.php`
  - `app/Views/layouts/app.php`
- Banco:
  - `database/migrations/004_children_count_backfill.sql`
- Documentacao:
  - `docs/MANUAL_CLIENTE.md`
  - `docs/TESTE_INTEGRADO_MVP.md`
  - `docs/sprints/sprint-33.md`

## Observacoes
- Nao houve alteracao no modulo `Pessoas`.
- Estrutura de dados foi mantida (`children` e `family_members` separados).
- Transferencia de crianca entre familias nao e suportada nesta fase.
