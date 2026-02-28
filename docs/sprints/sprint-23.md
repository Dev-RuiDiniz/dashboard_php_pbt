# Sprint 23 - Dashboard operacional real

## Entregas
- Substituicao dos placeholders do dashboard por indicadores reais do banco.
- Inclusao de cards operacionais com:
  - familias (total e ativas)
  - pessoas acompanhadas (total e em situacao de rua)
  - criancas cadastradas
  - entregas do mes (cestas retiradas e registros)
  - encaminhamentos do mes
  - equipamentos por status (disponivel, emprestado, manutencao, inativo)
- Inclusao de alertas operacionais no dashboard:
  - documentacao pendente
  - visitas pendentes
  - familias sem atualizacao ha mais de X dias
  - devolucoes de equipamentos atrasadas
- Inclusao de listas resumidas (top 5) para cada alerta com links de acao.
- Manutencao das acoes rapidas no painel.

## Entregaveis tecnicos
- Novo modelo:
  - `app/Models/DashboardModel.php`
- Atualizacao do controller:
  - `app/Controllers/DashboardController.php`
- Atualizacao da view:
  - `app/Views/dashboard/index.php`

## Regras/parametros usados
- Periodo mensal do dashboard:
  - inicio: primeiro dia do mes atual
  - fim: ultimo dia do mes atual
- Janela de alerta para familias sem atualizacao:
  - parametro `ALERT_STALE_DAYS` (via `config/app.php`)
