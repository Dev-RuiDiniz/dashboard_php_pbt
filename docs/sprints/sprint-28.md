# Sprint 28 - Consolidacao de fichas sociais e tema bege do dashboard

## Entregas
- Aba `Fichas Sociais` concluida no menu principal:
  - menu passa a apontar para `/social-records`
- Nova pagina consolidada de fichas sociais:
  - `GET /social-records`
  - filtros por texto, periodo, vinculo familiar e sinalizadores espirituais
  - cards de resumo (total, com familia, deseja oracao, aceita visita)
  - tabela operacional com acesso rapido ao detalhe da pessoa
- Consolidacao de dados no backend de fichas:
  - busca global de fichas sociais
  - resumo agregado respeitando os filtros da pagina
- Dashboard atualizado com identidade visual em tons de bege:
  - cards com gradiente/bege
  - contraste e botoes adaptados para o tema do painel
  - atalho rapido para a nova pagina de fichas sociais

## Entregaveis tecnicos
- Controller:
  - `app/Controllers/SocialRecordController.php`
- Model:
  - `app/Models/SocialRecordModel.php`
- Rotas:
  - `config/routes.php`
- Views:
  - `app/Views/social_records/index.php`
  - `app/Views/dashboard/index.php`
  - `app/Views/layouts/app.php`
- Estilo:
  - `public/assets/app.css`
- Documentacao:
  - `docs/README.md`
  - `docs/sprints/sprint-28.md`

## Observacoes
- A permissao utilizada para acesso de fichas sociais segue o mesmo controle de visualizacao de pessoas acompanhadas.
- O tema bege foi aplicado de forma escopada ao dashboard para nao impactar o restante do sistema.
