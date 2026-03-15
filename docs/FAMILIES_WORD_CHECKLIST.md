# Checklist de Requisitos (Word) - Modulo Familias

Fonte: `Especificacao_Sistema_Igreja_Social_PHP_MySQL.docx` (secoes 3.3 e 4.1)

## Objetivo operacional
- Reduzir digitacao no cadastro de familia para atendimento sob pressao.
- Preservar dados obrigatorios e consistencia para relatorios finais.

## Fluxo de Familia (obrigatorio)
- Tela de lista de familias com filtros por CPF, nome, bairro/cidade, status e pendencias.
- Tela de nova/edicao de familia com secoes claras.
- Tela de detalhe da familia com resumo operacional.

## Campos esperados (families)
- Responsavel: `responsible_name`.
- Documento: `cpf_responsible` (unico quando informado), `rg_responsible`.
- Contato: `phone`.
- Nascimento da responsavel: `birth_date` (idade calculada na tela).
- Endereco: `cep`, `address`, `address_number`, `address_complement`, `neighborhood`, `city`, `state`, `location_reference`.
- Socioeconomico: `marital_status`, `education_level`, `professional_status`, `profession_detail`, `housing_type`.
- Indicadores: `adults_count`, `workers_count`, `family_income_total`, `children_count`.
- Indicador adicional consolidado: `family_income_average`.
- Pendencias: `documentation_status`, `documentation_notes`, `needs_visit`, `general_notes`.

## Regras de negocio (familia)
- CPF:
  - validar formato quando informado;
  - bloquear duplicidade.
- Renda familiar:
  - somar rendas do principal + membros + dependentes para alimentar `family_income_total`;
  - calcular `family_income_average` per capita.
- Pendencias:
  - permitir registrar status de documentacao e necessidade de visita.

## Diretrizes de UX para cadastro rapido
- Priorizar selecao por listas controladas onde possivel.
- Aplicar mascara para campos documentais e telefone.
- Mostrar numero da familia para referencia operacional imediata.
- Evitar campos redundantes e reduzir texto explicativo longo.

## Criterios de aceite para esta entrega
- Cadastro de nova familia com menos digitacao manual.
- CPF, RG e telefone com melhor formato de entrada.
- Listas controladas para:
  - tipo de moradia;
  - estado civil;
  - escolaridade;
  - situacao profissional.
- Idade da responsavel calculada em tela sem persistencia adicional.
- Numero da familia exibido com base no `id` existente.
