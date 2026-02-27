# Deploy Hostinger - Checklist MVP

## 1) Preparacao local
- [ ] Atualizar branch principal com Sprint 20.
- [ ] Confirmar `.env` de producao (DB e APP_URL).
- [ ] Gerar backup do banco atual (se existir).

## 2) Estrutura de hospedagem
- [ ] Enviar projeto para servidor (Git/FTP).
- [ ] Garantir `public_html` apontando para pasta `public/`.
- [ ] Validar permissao de escrita em `storage/` (se usado para logs/temp).

## 3) PHP e extensoes
- [ ] PHP 8.2+ ativo.
- [ ] Extensoes habilitadas: `pdo_mysql`, `mbstring`, `openssl`, `json`.
- [ ] `session.cookie_httponly` ativo no ambiente.

## 4) Banco de dados
- [ ] Criar banco MySQL no painel Hostinger.
- [ ] Importar `database/final_mvp.sql` (primeiro deploy) ou rodar `php database/migrate.php`.
- [ ] Validar usuario admin seed e obrigar troca de senha no primeiro acesso operacional.

## 5) Configuracao de ambiente
- [ ] Criar `.env` com:
  - `APP_ENV=production`
  - `APP_DEBUG=false`
  - `APP_URL=https://SEU_DOMINIO`
  - credenciais reais de banco
  - parametros de seguranca de auth
- [ ] Nunca versionar `.env`.

## 6) Web server e roteamento
- [ ] Confirmar `public/.htaccess` ativo no Apache.
- [ ] Testar rota de saude: `GET /health` deve retornar `status: ok`.

## 7) Verificacao funcional (smoke)
- [ ] Login / logout.
- [ ] CRUD de familias e membros.
- [ ] Fluxo de entrega (evento + lista + status).
- [ ] Fluxo de emprestimo/devolucao de equipamento.
- [ ] Fluxo de visitas (solicitar/concluir).
- [ ] Relatorios + exportacao PDF.

## 8) Seguranca
- [ ] Validar headers de hardening no response.
- [ ] Validar bloqueio por tentativas de login.
- [ ] Validar recuperacao de senha por token.
- [ ] Validar registros em `audit_logs`.

## 9) Go-live
- [ ] Publicar versao inicial.
- [ ] Registrar data/hora da publicacao.
- [ ] Compartilhar credenciais operacionais com equipe responsavel.
- [ ] Definir rotina de backup diario do banco.
