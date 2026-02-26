# Migrations (simples)

Este projeto usa scripts SQL versionados em `database/migrations/`.

Ordem atual:

1. `001_schema_mvp.sql`
2. `002_seeds_initial.sql`

## Como aplicar (manual)

1. Execute `database/schema.sql`
2. Execute `database/seeds.sql`

## Como aplicar (script PHP)

Use:

```bash
php database/migrate.php
```

O script:

- carrega `.env`
- conecta via PDO
- cria tabela `schema_migrations` (se nao existir)
- aplica arquivos `.sql` numerados em ordem
- registra o nome do arquivo aplicado

Observacao: os arquivos versionados desta pasta sao wrappers de controle e o runner aplica `schema.sql` / `seeds.sql` diretamente com base no nome da migracao.

