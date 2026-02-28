# Sprint 25 - Geracao automatica de convidados e lista de criancas por evento

## Entregas
- Nova geracao automatica de convidados no evento de entrega por criterios operacionais.
- Criterios adicionados na tela do evento:
  - cidade
  - bairro (busca parcial)
  - apenas documentacao pendente/parcial
  - apenas familias com visita pendente
  - apenas familias com criancas
  - renda maxima familiar
  - limite de familias e quantidade de cestas por familia
- Validacoes operacionais na geracao automatica:
  - nao adiciona familia duplicada no mesmo evento
  - respeita bloqueio de retirada no mesmo mes (quando ativo no evento)
  - respeita limite maximo de cestas do evento
  - gera senha sequencial por evento
- Inclusao de auditoria para execucao da geracao automatica (`delivery.auto_generate`).
- Inclusao da lista de criancas vinculadas ao evento, baseada nas familias convidadas na lista operacional.

## Entregaveis tecnicos
- Controller:
  - `app/Controllers/DeliveryEventController.php`
- Models:
  - `app/Models/DeliveryModel.php`
  - `app/Models/ChildModel.php`
- Rotas:
  - `config/routes.php`
- View:
  - `app/Views/delivery_events/show.php`
- Documentacao:
  - `docs/README.md`
  - `docs/sprints/sprint-25.md`

## Observacoes
- A geracao automatica usa apenas familias ativas (`is_active = 1`).
- Quando nenhum convidado e gerado, a tela retorna feedback com orientacao para revisar filtros e bloqueios.
