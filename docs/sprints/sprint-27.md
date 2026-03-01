# Sprint 27 - Reabertura, filtros operacionais e impressao da lista

## Entregas
- Reabertura de evento concluido:
  - `POST /delivery-events/reopen?id={event_id}`
  - regra: apenas eventos em `concluido` podem ser reabertos
  - evento reaberto volta para status `aberto`
- Filtros na lista operacional do evento:
  - busca por senha, nome do convidado ou documento
  - filtro por status (`nao_veio`, `presente`, `retirou`)
  - indicador de quantidade exibida (filtrado vs total do evento)
- Exportacao CSV da lista operacional com filtros aplicados:
  - `GET /delivery-events/deliveries/csv?event_id={event_id}&q={...}&status={...}`
- Impressao da lista operacional com filtros aplicados:
  - `GET /delivery-events/print?event_id={event_id}&q={...}&status={...}`
  - layout dedicado para impressao com resumo e tabela
- Atualizacao da UX da tela operacional:
  - botoes de `Exportar CSV` e `Imprimir lista` com preservacao dos filtros
  - botao `Reabrir evento` para eventos concluidos

## Entregaveis tecnicos
- Controller:
  - `app/Controllers/DeliveryEventController.php`
- Models:
  - `app/Models/DeliveryModel.php`
- Rotas:
  - `config/routes.php`
- Views:
  - `app/Views/delivery_events/show.php`
  - `app/Views/delivery_events/print.php`
- Documentacao:
  - `docs/README.md`
  - `docs/sprints/sprint-27.md`

## Auditoria
- Novas acoes registradas:
  - `delivery_event.reopen`
  - `delivery.print_list`
- Exportacao CSV por evento passa a registrar filtros utilizados.
