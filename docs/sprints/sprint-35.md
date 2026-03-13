# Sprint 35 - Cadastro completo de membros na aba Familia

## Entregas
- Expansao do fluxo unificado de `Adicionar pessoa` em `families/show` para incluir:
  - `Principal`
  - `Membro`
  - `Dependente`
  - `Crianca`
- Atualizacao do principal diretamente na aba familia, sem navegar para tela de edicao completa.
- Manutencao de compatibilidade:
  - principal continua persistido em `families` (`responsible_*`)
  - membro/dependente continuam em `family_members`
  - crianca continua em `children`

## Entregaveis tecnicos
- Backend:
  - `app/Controllers/FamilyController.php`
  - `app/Models/FamilyModel.php`
  - `config/routes.php`
- Interface:
  - `app/Views/families/show.php`
  - `public/assets/family-form-enhancements.js`
- Documentacao:
  - `docs/MANUAL_CLIENTE.md`
  - `docs/TESTE_INTEGRADO_MVP.md`
  - `docs/sprints/sprint-35.md`
