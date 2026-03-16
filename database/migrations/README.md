# Migrations (simples)

Este projeto usa scripts SQL versionados em `database/migrations/`.

Ordem atual:

1. `001_schema_mvp.sql`
2. `002_seeds_initial.sql`
3. `003_security_hardening.sql`
4. `004_children_count_backfill.sql`
5. `005_family_people_documents.sql`
6. `006_family_income_average_and_principal_work.sql`

## Como aplicar (manual)

Banco novo:

1. Importe `database/final_mvp.sql`

Banco existente:

1. Execute `database/schema.sql` apenas se estiver reconstruindo do zero
2. Para atualizacoes incrementais, aplique as migrations pendentes em ordem ou rode o script PHP abaixo

## Como aplicar (script PHP)

Use:

```bash
php database/migrate.php
```

O script:

- carrega `.env` e `.env.local`
- conecta via PDO
- cria tabela `schema_migrations` (se nao existir)
- aplica arquivos `.sql` numerados em ordem
- registra o nome do arquivo aplicado

Observacoes:

- os arquivos versionados desta pasta sao wrappers de controle e o runner aplica `schema.sql` / `seeds.sql` diretamente com base no nome da migracao
- `database/final_mvp.sql` ja traz o baseline atual e popula `schema_migrations`, entao uma base nova importada por ele fica pronta para futuras execucoes de `php database/migrate.php`
