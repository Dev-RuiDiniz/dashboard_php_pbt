# Sprint 32 - Cadastro rapido de familias para entrevistas

## Entregas
- Alinhamento de requisitos do modulo de familias com base no documento de especificacao:
  - checklist dedicado para implementacao e aceite operacional
- Melhorias de digitacao no formulario de familia:
  - mascara automatica para CPF, RG e Telefone
  - campo de idade calculada em tela pela data de nascimento
- Padronizacao de listas controladas no cadastro:
  - tipo de moradia
  - estado civil
  - escolaridade
  - situacao profissional
  - fallback de valor legado para cadastros antigos na edicao
- Identificacao operacional da familia:
  - exibicao de numero da familia com base no ID
  - destaque na listagem e no detalhe
  - indicador de total cadastrado na listagem
- Reforco de validacao no backend:
  - saneamento de RG e telefone
  - validacao de valores permitidos nas listas controladas

## Entregaveis tecnicos
- Backend:
  - `app/Controllers/FamilyController.php`
  - `app/Models/FamilyModel.php`
- Interface:
  - `app/Views/families/form.php`
  - `app/Views/families/index.php`
  - `app/Views/families/show.php`
  - `app/Views/layouts/app.php`
  - `public/assets/family-form-enhancements.js`
- Documentacao:
  - `docs/FAMILIES_WORD_CHECKLIST.md`
  - `docs/MANUAL_CLIENTE.md`
  - `docs/TESTE_INTEGRADO_MVP.md`
  - `docs/sprints/sprint-32.md`

## Observacoes
- Nao houve alteracao de `.env`.
- Nao houve criacao de nova rota publica.
- O numero da familia utiliza o `id` existente, sem migracao de banco.
