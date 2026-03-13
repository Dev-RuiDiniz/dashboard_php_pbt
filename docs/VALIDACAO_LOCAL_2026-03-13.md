# Validacao Local - 13/03/2026

## Escopo executado
- Aplicacao de migracoes no ambiente local.
- Validacao do fluxo completo de Familia:
  - principal
  - membro
  - dependente
  - crianca
- Validacao de bloqueio de CPF duplicado no cadastro de Pessoas.

## Banco local (aplicado)
### Base `dashboard_php_pbt` (`.env.local`)
- `003_security_hardening.sql` marcado como aplicado por compatibilidade (schema ja consolidado).
- `004_children_count_backfill.sql` executado.
- `005_family_people_documents.sql` executado.

### Base usada pela aplicacao local em execucao (`.env`)
- `003_security_hardening.sql` marcado como aplicado por compatibilidade (schema ja consolidado).
- `004_children_count_backfill.sql` executado.
- `005_family_people_documents.sql` executado.

## Validacao funcional realizada
- Login admin no ambiente local (`http://127.0.0.1:8000`).
- Criacao de familia com `CPF/RG` obrigatorios da responsavel.
- Inclusao de `Membro` com `CPF/RG`.
- Inclusao de `Dependente` com `CPF/RG`.
- Inclusao de `Crianca` com documentos opcionais.
- Confirmacao de recalc automatico na pagina da familia:
  - `adults_count`
  - `workers_count`
  - `family_income_total`
  - `children_count`
- Confirmacao de idade automatica em tela (membro/crianca).
- Tentativa de cadastrar Pessoa com CPF ja usado em membro da familia:
  - resultado esperado: bloqueado
  - resultado obtido: bloqueado (registro nao persistido)

## Ajuste tecnico necessario identificado e aplicado
- Corrigido `findCpfConflict` em `FamilyModel` para evitar erro PDO (`Invalid parameter number`) causado por placeholders repetidos na query unificada de conflito de CPF.

## Evidencia
- Execucao de validacao automatizada local finalizou com:
  - `RESULTADO_VALIDACAO: OK`
