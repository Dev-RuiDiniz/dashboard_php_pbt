# Sprint 29 - Modernizacao visual global em branco e dourado

## Entregas
- Redesign visual global do sistema com identidade elegante em branco e dourado:
  - nova paleta base, contrastes e sombras suaves
  - tipografia mais refinada para titulos e leitura
  - ajuste de fundos da aplicacao e autenticacao
- Atualizacao do layout principal:
  - sidebar clara com destaque dourado para estado ativo
  - header e footer com visual leve e acabamento premium
  - padronizacao de logos no topo e nas telas de autenticacao
- Padronizacao dos componentes de interface:
  - cards e metricas com degrades claros e bordas harmonizadas
  - botoes primarios/secundarios com variacoes em dourado
  - formularios com foco visual consistente
  - tabelas, listas, badges e alerts alinhados ao novo tema
- Refino visual em telas fora do layout principal:
  - login, recuperar senha e redefinir senha
  - pagina inicial (`/`)
  - tela de impressao operacional de eventos

## Entregaveis tecnicos
- Estilo global:
  - `public/assets/app.css`
- Views de autenticacao:
  - `app/Views/auth/login.php`
  - `app/Views/auth/forgot_password.php`
  - `app/Views/auth/reset_password.php`
- Outras views:
  - `app/Views/home.php`
  - `app/Views/delivery_events/print.php`
- Documentacao:
  - `docs/README.md`
  - `docs/sprints/sprint-29.md`

## Observacoes
- O tema foi aplicado preservando as classes existentes para evitar impacto em rotas e regras de negocio.
- A acao destrutiva (`Concluir evento`) permanece com destaque de risco para manter seguranca operacional.
