# Sprint 26 - Fechamento operacional de eventos de entrega

## Entregas
- Inclusao de fechamento formal do evento de entrega com acao dedicada:
  - `POST /delivery-events/close?id={event_id}`
- Regra de fechamento:
  - nao permite concluir evento com convidados em status `presente` aguardando retirada
  - ao concluir, o evento passa para status `concluido`
- Inclusao de exportacao CSV da lista operacional por evento:
  - `GET /delivery-events/deliveries/csv?event_id={event_id}`
  - exporta cabecalho do evento, resumo e lista de convidados/entregas
- Inclusao de cards de resumo operacional na tela do evento:
  - total de registros
  - total em `nao_veio`
  - total em `presente`
  - total de cestas retiradas
- Ajustes de UX na lista operacional:
  - botao de concluir evento na tela
  - botao de exportar CSV na tela
  - bloqueio visual de geracao/alteracao quando o evento estiver concluido
- Auditoria adicionada:
  - `delivery_event.close`
  - `delivery.export_csv_event`

## Entregaveis tecnicos
- Controller:
  - `app/Controllers/DeliveryEventController.php`
- Models:
  - `app/Models/DeliveryEventModel.php`
  - `app/Models/DeliveryModel.php`
- Rotas:
  - `config/routes.php`
- View:
  - `app/Views/delivery_events/show.php`
- Documentacao:
  - `docs/README.md`
  - `docs/sprints/sprint-26.md`

## Observacoes
- O fechamento de evento fortalece o fluxo operacional (impede novas alteracoes apos encerramento).
- A exportacao CSV usa separador `;` e inclui BOM UTF-8 para melhor compatibilidade com planilhas.
