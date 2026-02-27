# Sprint 16 - Emprestimos e devolucoes

## Entregas
- Fluxo de emprestimo com `equipment_loans`.
- Fluxo de devolucao de emprestimos em aberto.
- Atualizacao automatica do status do equipamento:
  - ao emprestar: `emprestado`
  - ao devolver: `disponivel`
- Atualizacao automatica do estado de conservacao na devolucao.
- Alertas de devolucao atrasada na tela operacional de emprestimos.

## Rotas
- `GET /equipment-loans`
- `POST /equipment-loans`
- `POST /equipment-loans/return?id={loan_id}`

