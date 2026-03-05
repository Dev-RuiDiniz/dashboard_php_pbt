# Sprint 31 - Restricao de acesso a moradores de rua e ajustes de producao

## Entregas
- Restricao de acesso do modulo de moradores de rua para perfil administrador:
  - rotas de `Pessoas` e `Fichas Sociais` movidas para middleware `adminOnly`
  - bloqueio aplicado para listagem, detalhe, cadastro, edicao e exclusao
  - bloqueio aplicado para atendimentos sociais, encaminhamentos e acompanhamentos espirituais
- Ajustes de interface por perfil:
  - menu lateral/mobile exibe `Pessoas` e `Fichas Sociais` somente para `admin`
  - dashboard oculta atalhos de `Novo atendimento` e `Fichas sociais` para perfis nao admin
  - dashboard exibe contagem de pessoas em situacao de rua apenas para `admin`
- Ajuste de ambiente de producao (local, nao versionado):
  - `.env` atualizado com host `127.0.0.1`, porta `3306`, banco e usuario da Hostinger informados pelo cliente
  - senha de banco mantida somente no `.env` local (arquivo ignorado pelo git)

## Entregaveis tecnicos
- Backend de autorizacao por rota:
  - `config/routes.php`
- Interface:
  - `app/Views/layouts/app.php`
  - `app/Views/dashboard/index.php`
- Documentacao:
  - `docs/MANUAL_CLIENTE.md`
  - `docs/README.md`
  - `docs/sprints/sprint-31.md`

## Observacoes
- O controle foi implementado no backend (rotas), garantindo bloqueio mesmo com acesso direto por URL.
- O cadastro de credenciais de banco em producao continua fora do versionamento por seguranca.
