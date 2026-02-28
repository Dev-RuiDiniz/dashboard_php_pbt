# Sprint 24 - Relatorios completos e exportacoes CSV/Excel

## Entregas
- Ampliacao do modulo de relatorios com dois novos blocos:
  - equipamentos (emprestados, devolvidos, atrasados e em manutencao)
  - pendencias (documentacao, visitas, devolucoes atrasadas e sem atualizacao)
- Atualizacao da tela de relatorios com:
  - novos cards de resumo
  - tabelas/amostras de equipamentos e pendencias
  - novos botoes de exportacao
- Exportacoes adicionadas:
  - `GET /reports/csv`
  - `GET /reports/excel`
  - alias: `GET /reports/xlsx`
- Exportacao PDF atualizada para incluir equipamentos e pendencias.
- Auditoria de exportacoes estendida para:
  - `report.export_csv`
  - `report.export_excel`

## Entregaveis tecnicos
- Modelo:
  - `app/Models/ReportModel.php`
- Controller:
  - `app/Controllers/ReportController.php`
- Rotas:
  - `config/routes.php`
- Views:
  - `app/Views/reports/index.php`
  - `app/Views/reports/pdf.php`

## Observacoes
- Exportacao Excel implementada em formato compativel (`.xls`) para leitura em planilhas.
- Endpoint `/reports/xlsx` mantido como alias operacional para o mesmo exportador Excel.
