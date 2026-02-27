# Sprint 17 - Visitas e pendencias

## Entregas
- CRUD de `visits`.
- Fluxo de solicitar visita.
- Fluxo de concluir visita.
- Alertas de pendencias:
  - documentacao pendente/parcial de familias
  - visitas pendentes/agendadas
  - itens sem atualizacao (familias/pessoas)
- Parametro de tempo sem atualizacao via `ALERT_STALE_DAYS`.

## Rotas
- `GET /visits`
- `GET /visits/create`
- `POST /visits`
- `GET /visits/edit?id={visit_id}`
- `POST /visits/update?id={visit_id}`
- `POST /visits/conclude?id={visit_id}`
- `POST /visits/delete?id={visit_id}`

