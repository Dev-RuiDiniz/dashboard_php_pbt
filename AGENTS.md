# Regras para trabalhar neste repositório

Estas instruções orientam manutenção e execução do Dashboard PHP PBT / Sistema Igreja Social. Elas não substituem instruções superiores nem pedidos explícitos do usuário.

## 1. Antes de alterar

- Leia este arquivo e consulte a documentação ligada à tarefa.
- Use o código e o schema atuais como referência. Relatórios de sprint são histórico e podem descrever etapas já substituídas.
- Faça a menor mudança que resolva o pedido. Não crie módulos, rotas ou regras sem requisito confirmado.
- Ao encontrar conflito entre documento antigo e código, registre a diferença antes de atualizar a regra do produto.

## 2. Arquitetura

- PHP 8.2+, Composer, MySQL/MariaDB, HTML renderizado no servidor e Bootstrap.
- `public/index.php` é o front controller; rotas ficam em `config/routes.php`.
- Separe responsabilidade entre `Controllers`, `Services`, `Models`, `Middlewares` e `Views`.
- Views exibem dados; consultas e regras de negócio ficam no servidor.
- Use PDO com prepared statements. Valide e normalize entradas no backend mesmo quando houver validação visual.
- Preserve o controle de permissões por rota. Ocultar uma opção no menu não substitui bloqueio no servidor.

## 3. Banco de dados

- Mantenha migrations numeradas em `database/migrations/` e aplique-as em ordem.
- Ao alterar schema, atualize a migration, `database/schema.sql` e o baseline `database/final_mvp.sql` conforme o padrão existente.
- Não altere banco de produção diretamente durante desenvolvimento.
- A instalação inicial usa `database/final_mvp.sql`; atualizações usam o runner de migrations ou o procedimento de publicação aprovado.

## 4. Idioma, interface e documentação

- Escreva interfaces, mensagens e documentação em português do Brasil.
- Preserve nomes oficiais de classes, rotas, tabelas e identificadores.
- Use texto simples para orientar operadores; não prometa recursos que o código não oferece.
- Atualize o PRD e o ROADMAP quando uma regra ou etapa mudar.

## 5. Credenciais e dados

- Nunca inclua senhas, tokens, `.env`, `.env.local`, dados pessoais ou cópias do banco em commits, imagens ou logs públicos.
- `ACESSOS.md` é local e ignorado pelo Git e pelo Docker. Não remova essa proteção.
- Não copie credenciais de produção para exemplos ou documentação versionada. No servidor, mantenha-as apenas no arquivo de ambiente protegido.
- O manual do cliente atual contém credenciais de seed. A sincronização desse manual com GitHub foi excluída; não o publique sem autorização específica.
- Use apenas dados de exemplo no Docker local; não importe a base real da VPS para desenvolvimento.

## 6. Docker local

- Subir/atualizar: `docker compose -p dashboard-php-pbt-local up -d --build`.
- Aplicação: `http://localhost:8093`.
- Parar preservando o banco: `docker compose -p dashboard-php-pbt-local down`.
- Não use `down -v` sem pedido explícito: isso apaga os volumes locais do banco e dos arquivos.
- O banco não deve ter porta publicada no host; mantenha a aplicação vinculada a `127.0.0.1`.

## 7. Verificação e publicação

- Faça as verificações solicitadas para a tarefa e descreva exatamente o que foi ou não conferido.
- Antes de publicar, confira o diff, a configuração de ambiente, o baseline de banco e o checklist correspondente em `docs/`.
- Para produção, exija backup válido, revisão de migrations, smoke test pós-publicação e verificação de `/health`.
- Não publique na VPS ou em repositório remoto apenas porque a mudança funciona localmente; siga a autorização de publicação vigente na conversa.
- Quando não houver convenção mais específica, escreva commits em pt-BR com Conventional Commits, por exemplo `fix(familias): exibir campos condicionais`.
