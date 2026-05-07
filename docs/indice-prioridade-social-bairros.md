# Índice de prioridade social por bairro

## Objetivo

O índice de prioridade social por bairro apoia a tomada de decisão no dashboard, indicando bairros de Taubaté que podem demandar mais atenção operacional do projeto social.

A métrica substitui a interpretação anterior de “% carência”, que era baseada em famílias com retirada sobre famílias ativas. Esse cálculo indicava cobertura histórica de atendimento, não carência social.

## Escopo da regra

O índice considera somente famílias ativas com bairro preenchido e cidade normalizada como Taubaté.

A normalização por bairro usa o valor textual do cadastro da família agrupado por `UPPER(TRIM(neighborhood))`, reduzindo diferenças simples de maiúsculas, minúsculas e espaços.

## Fórmula atual

```text
priority_score =
  familias_sem_retirada * 3
+ visitas_pendentes * 2
+ documentacao_pendente * 2
+ familias_sem_atualizacao * 1
+ pessoas_em_situacao_de_rua * 3
+ criancas_vinculadas * 1
```

## Indicadores usados

| Indicador | Descrição | Peso |
|---|---|---:|
| famílias sem retirada | Famílias ativas do bairro sem registro de retirada concluída | 3 |
| visitas pendentes | Visitas com status pendente vinculadas a famílias do bairro | 2 |
| documentação pendente | Famílias ativas cujo status de documentação não está como `ok` | 2 |
| famílias sem atualização | Famílias ativas sem atualização há mais dias que o limite configurado no alerta | 1 |
| pessoas em situação de rua | Pessoas marcadas como situação de rua e vinculadas por ficha social a famílias do bairro | 3 |
| crianças vinculadas | Crianças cadastradas nas famílias ativas do bairro | 1 |

## Interpretação

Quanto maior o score, maior a prioridade sugerida para acompanhamento.

O índice é um apoio operacional e não deve ser tratado como diagnóstico definitivo de vulnerabilidade social. A equipe deve revisar o contexto das famílias e dos bairros antes de tomar decisões sensíveis.

## Limitações conhecidas

- O score depende da qualidade dos cadastros de bairro, cidade e vínculos familiares.
- Famílias sem retirada podem estar nessa condição por motivos diversos, como cadastro recente, ausência em evento ou inconsistência de registro.
- Os pesos são uma regra inicial e podem ser ajustados conforme a prática do projeto.
- A classificação por bairro pode esconder diferenças importantes entre famílias do mesmo território.

## Critérios de revisão futura

Revisar os pesos quando houver evidência de que algum fator está superestimado ou subestimado.

Possíveis evoluções:

- considerar renda familiar média;
- considerar doenças crônicas, deficiência e uso contínuo de medicação;
- diferenciar crianças por faixa etária;
- considerar tempo desde a última retirada;
- criar parametrização dos pesos em configuração.
