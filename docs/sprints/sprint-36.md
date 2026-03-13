# Sprint 36 - Automacoes de idade/renda e documentos completos na Familia

## Entregas
- Campos de documento (CPF/RG) adicionados para membros/dependentes e criancas.
- Validacao operacional por tipo:
  - Principal, membro e dependente com CPF/RG obrigatorios.
  - Crianca com CPF/RG opcionais (com validacao quando preenchidos).
- Regra de CPF unico no sistema aplicada no fluxo de familia, com bloqueio de duplicidade entre:
  - `families.cpf_responsible`
  - `family_members.cpf`
  - `children.cpf`
  - `people.cpf`
- Indicadores da familia (`adults_count`, `workers_count`, `family_income_total`, `children_count`) mantidos como derivados do cadastro de pessoas da familia, sem edicao manual no formulario.
- Automacao de idade:
  - exibicao automatica em tela para principal, membro/dependente e crianca.
  - persistencia automatica de `children.age_years` com base em `birth_date`.

## Entregaveis tecnicos
- Banco:
  - `database/schema.sql`
  - `database/final_mvp.sql`
  - `database/migrations/005_family_people_documents.sql`
  - `database/migrations/README.md`
- Backend:
  - `app/Controllers/FamilyController.php`
  - `app/Controllers/ChildController.php`
  - `app/Models/FamilyModel.php`
  - `app/Models/ChildModel.php`
- Interface:
  - `app/Views/families/form.php`
  - `app/Views/families/show.php`
  - `public/assets/family-form-enhancements.js`
- Documentacao:
  - `docs/MANUAL_CLIENTE.md`
  - `docs/TESTE_INTEGRADO_MVP.md`
  - `docs/sprints/sprint-36.md`
  - `docs/VALIDACAO_LOCAL_2026-03-13.md`
