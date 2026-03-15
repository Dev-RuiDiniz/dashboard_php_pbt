# Sprint 37 - Abas completas e consolidacao da composicao familiar

## Entregas
- Refatoracao do modulo de familias para servicos dedicados:
  - `FamilyRegistrationService`
  - `FamilyCompositionService`
  - `FamilyIndicatorsService`
  - `FamilyDetailService`
- Detalhe da familia reorganizado em abas:
  - `Composicao Familiar`
  - `Resumo`
  - `Entregas`
  - `Emprestimos`
  - `Visitas/Anotacoes`
  - `Pendencias`
- Fluxo explicito de `Dependente` na UI, com persistencia em `family_members` usando parentesco `Dependente`.
- Inclusao de `responsible_works` e `responsible_income` no cadastro do principal.
- Novo indicador `family_income_average` com regra per capita.
- Indicadores da familia passam a incluir o principal em renda, trabalhadores e adultos quando aplicavel.

## Entregaveis tecnicos
- Banco:
  - `database/schema.sql`
  - `database/final_mvp.sql`
  - `database/seeds.sql`
  - `database/migrations/006_family_income_average_and_principal_work.sql`
- Backend:
  - `app/Controllers/FamilyController.php`
  - `app/Models/FamilyModel.php`
  - `app/Models/DeliveryModel.php`
  - `app/Models/EquipmentLoanModel.php`
  - `app/Models/VisitModel.php`
  - `app/Services/Family*.php`
- Interface:
  - `app/Views/families/show.php`
  - `app/Views/families/form.php`
  - `app/Views/families/index.php`
  - `public/assets/family-form-enhancements.js`

## Validacao
- `php -l` completo: `OK`
- Smoke test local com bootstrap temporario para autoload:
  - `/health`: `200`
  - `/login`: `200`
