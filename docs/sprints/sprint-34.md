# Sprint 34 - Cadastro unificado de pessoas na aba Familia

## Entregas
- Consolidacao do fluxo de cadastro de pessoas no detalhe da familia:
  - botao unico `Adicionar pessoa`
  - selecao de tipo inline: `Membro`, `Dependente`, `Crianca`
- Manutencao do principal da familia nos campos da entidade `families` (sem duplicacao em `family_members`).
- Persistencia de `Dependente` sem migracao estrutural:
  - uso de `family_members.relationship = 'Dependente'`
- Edicao e exclusao de membro/dependente/crianca na mesma pagina da familia.
- Compatibilidade preservada:
  - sem novas rotas publicas
  - modulo `Pessoas` inalterado
  - `children_count` continua derivado automaticamente de `children`

## Entregaveis tecnicos
- Backend:
  - `app/Controllers/FamilyController.php`
- Interface:
  - `app/Views/families/show.php`
  - `public/assets/family-form-enhancements.js`
- Documentacao:
  - `README.md`
  - `docs/MANUAL_CLIENTE.md`
  - `docs/TESTE_INTEGRADO_MVP.md`
  - `docs/sprints/sprint-34.md`

## Observacoes
- Nao houve alteracao de `.env`.
- Nao houve migracao de banco nesta sprint.
- Fluxo legado de criancas (`/children*`) permanece apenas como redirecionamento de compatibilidade.
