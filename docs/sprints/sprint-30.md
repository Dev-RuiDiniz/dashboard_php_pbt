# Sprint 30 - Consolidacao responsiva para web e mobile

## Entregas
- Consolidacao da experiencia responsiva do sistema:
  - navegacao mobile com menu lateral em offcanvas
  - preservacao do sidebar fixo em desktop
  - melhor aproveitamento de espaco no header e no conteudo
- Ajustes globais de responsividade em componentes:
  - breakpoints refinados para desktop, tablet e celular
  - reducao progressiva de paddings e tipografia em telas menores
  - acoes principais empilhadas em mobile para melhor toque
- Tabelas consolidadas para uso em celulares:
  - padronizacao visual do container `.table-responsive`
  - rolagem horizontal com min-width para manter legibilidade
  - cobertura aplicada nas tabelas de relatorios que estavam sem wrapper responsivo
- Consolidacao visual sem impacto funcional:
  - mantidas rotas, regras de negocio e fluxos existentes
  - foco exclusivo em usabilidade e adaptacao de interface

## Entregaveis tecnicos
- Layout principal:
  - `app/Views/layouts/app.php`
- Relatorios:
  - `app/Views/reports/index.php`
- Estilo global:
  - `public/assets/app.css`
- Documentacao:
  - `docs/README.md`
  - `docs/sprints/sprint-30.md`

## Observacoes
- A navegacao mobile agora usa o mesmo conjunto de menus do desktop, mantendo consistencia funcional.
- As tabelas continuam priorizando leitura dos dados, com scroll horizontal controlado em telas pequenas.
