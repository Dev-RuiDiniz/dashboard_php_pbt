# Sprint 18 - Relatorios e exportacoes

## Entregas
- Relatorios mensais de:
  - familias
  - cestas
  - criancas
  - encaminhamentos
- Exportacao PDF dos relatorios consolidados.
- Filtros por:
  - periodo (`period_start` e `period_end`)
  - status
  - bairro

## Rotas
- `GET /reports`
- `GET /reports/pdf`

## Dependencias
- `dompdf/dompdf`
- `symfony/polyfill-mbstring`

