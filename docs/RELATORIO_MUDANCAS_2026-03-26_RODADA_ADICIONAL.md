# Relatorio de Mudancas - Rodada Adicional de 26/03/2026

## Escopo entregue
Esta rodada adicional consolidou tres frentes operacionais:

1. multiplos telefones em `Familias` e `PROJETO AMOR`
2. `valor do aluguel` para moradia `Alugada`
3. ajuste fino dos campos de saude e beneficio conforme a especificacao operacional mais recente

Os commits desta rodada foram:

- `9e32c19` `feat(contacts): support multiple phones with labels for families and projeto amor`
- `1522779` `feat(families): add rent amount for rented housing`
- `f10881a` `feat(cadastros): align health and benefit fields with operational spec`

## 1. Telefones multiplos

### O que mudou
- Criadas as tabelas `family_phones` e `person_phones`.
- Cada telefone agora registra:
  - `number`
  - `label`
  - `sort_order`
  - `is_primary`
- Os campos legados `families.phone` e `people.phone` foram mantidos.

### Como funciona no sistema
- Nos formularios de `Nova Familia`, `Editar Familia`, `Nova Pessoa` e `Editar Pessoa` o operador pode:
  - adicionar varios numeros
  - informar uma observacao curta para cada numero
  - marcar um telefone como principal
- Exemplos de observacao:
  - `recado com a filha`
  - `neta`
  - `vizinha`
- Ao salvar:
  - todos os telefones vao para a nova tabela filha
  - o telefone principal tambem e copiado para o campo legado `phone`
- Ao abrir um cadastro antigo:
  - se ele ainda nao tiver registros nas novas tabelas
  - o telefone legado aparece automaticamente como primeira linha no formulario

### Impacto operacional
- A equipe pode registrar mais de um contato por familia ou pessoa acompanhada sem perder compatibilidade com telas antigas.
- As listas continuam usando o telefone principal.
- Os detalhes mostram todos os telefones cadastrados.

## 2. Moradia alugada com valor do aluguel

### O que mudou
- Adicionada a coluna `families.rent_amount`.
- Criada a migration `011_family_rent_amount.sql`.

### Como funciona no sistema
- No formulario de familia:
  - ao selecionar `Tipo de moradia = Alugada`
  - o campo `Valor do aluguel` aparece automaticamente
- Ao salvar:
  - o valor e normalizado para formato monetario
  - e gravado em `rent_amount`
- Se o tipo de moradia for alterado para qualquer valor diferente de `Alugada`:
  - o campo e ocultado
  - o valor e limpo no backend

### Onde aparece
- No `Resumo operacional da familia`, o valor do aluguel e exibido quando aplicavel.

## 3. Campos de saude e beneficio alinhados

### Doenca cronica
As opcoes atuais passaram a ser:

- `Hipertensao`
- `Diabetes`
- `Doencas cardiovasculares`
- `Obesidade`
- `Doenca osteomuscular`
- `Depressao`
- `Transtornos mentais`

Compatibilidade:

- o valor legado `Depressao e Transtornos Mentais` continua editavel quando ja existir em registros antigos
- nao foi feito remapeamento em massa

### Deficiencia fisica
Antes:

- checkbox simples

Agora:

- seletor explicito `Nao` / `Sim`
- quando `Sim`, aparece o campo `Qual deficiencia?`
- quando `Nao`, o detalhe e limpo automaticamente ao salvar

### Medicacao continua
Antes:

- checkbox simples

Agora:

- seletor explicito `Nao` / `Sim`
- quando `Sim`, aparece o campo `Qual(is) medicacao(oes)?`
- quando `Nao`, o detalhe e limpo automaticamente ao salvar

### Beneficio social
Mantidas as opcoes:

- `Bolsa Familia`
- `Beneficio de Prestacao Continuada (BPC/LOAS)`
- `Tarifa Social de Energia Eletrica`
- `Aposentadoria`

## Arquivos principais afetados
- `app/Services/FamilyDataSupport.php`
- `app/Services/FamilyRegistrationService.php`
- `app/Services/FamilyCompositionService.php`
- `app/Services/FamilyDetailService.php`
- `app/Controllers/FamilyController.php`
- `app/Controllers/PersonController.php`
- `app/Models/FamilyModel.php`
- `app/Models/PersonModel.php`
- `app/Views/families/form.php`
- `app/Views/families/show.php`
- `app/Views/people/form.php`
- `app/Views/people/show.php`
- `app/Views/people/index.php`
- `database/schema.sql`
- `database/final_mvp.sql`
- `database/migrations/010_family_and_person_multiple_phones.sql`
- `database/migrations/011_family_rent_amount.sql`

## Verificacao executada
- `php -l` executado nos arquivos PHP alterados nesta rodada
- sem erros de sintaxe

## Observacoes
- Arquivos transitórios de `.local/mysql` nao fazem parte desta entrega e nao devem ser incluidos em commit.
- O pedido de imagem nao entrou nesta rodada porque nao foi repetido no plano aprovado mais recente.
