# Publicacao Inicial - MVP

## Escopo publicado
- Modulos ativos:
  - autenticacao e seguranca
  - usuarios e RBAC
  - familias, membros e criancas
  - pessoas acompanhadas e ficha social
  - encaminhamentos e acompanhamento espiritual
  - eventos e operacao de entregas
  - equipamentos, emprestimos e devolucoes
  - visitas e pendencias
  - relatorios e exportacao PDF

## Pacote de publicacao
- Aplicacao: branch `main` com Sprint 20.
- Banco inicial: `database/final_mvp.sql`.
- Checklist de deploy: `docs/DEPLOY_HOSTINGER_CHECKLIST.md`.
- Operacao diaria: `docs/OPERACAO_MVP.md`.

## Validacoes minimas pos-publicacao
- `GET /health` retornando `status=ok`.
- Login admin funcional.
- Fluxos principais validados conforme `docs/TESTE_INTEGRADO_MVP.md`.
- `audit_logs` recebendo novos eventos.

## Plano de suporte inicial
- Janela de monitoramento intensivo: primeiros 7 dias.
- Revisao diaria de erros operacionais e bloqueios de login.
- Backup diario do banco com retencao minima de 7 versoes.

