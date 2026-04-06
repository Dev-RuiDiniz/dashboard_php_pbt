# Relatorio de Mudancas - 26/03/2026

## Escopo
Pacote de correcoes operacionais no Dashboard PHP PBT para familias, PROJETO AMOR, entregas de cestas e equipamentos hospitalares.

## Tarefa 1 - PROJETO AMOR e datas de cadastro
- Renomeado o item visivel do menu lateral de `Pessoas` para `PROJETO AMOR`.
- Renomeado o titulo principal da listagem de pessoas para `PROJETO AMOR`, mantendo `Pessoas Acompanhadas` dentro de `Nova Pessoa`.
- Exibidas `Data de cadastro` e `Ultima atualizacao` em familias e pessoas.

## Tarefa 2 - Familias, RG opcional e composicao familiar
- Removida a obrigatoriedade de RG para responsavel, membros e criancas.
- Mantida validacao de formato apenas quando o RG for informado.
- Adicionado alerta de `documentacao pendente` no detalhe da familia.
- Adicionado campo `Estuda?` para membros e criancas.
- Mantido `Trabalha?` para maiores de idade na composicao familiar.
- Ajustado o fluxo da composicao familiar para facilitar correcao de cadastro feito como crianca.

## Tarefa 3 - Saude e beneficios em Familia e PROJETO AMOR
- Adicionados no cadastro base:
  - doenca cronica
  - deficiencia fisica e detalhamento
  - medicacao continua e detalhamento
  - beneficio social
- No PROJETO AMOR, adicionados tambem:
  - idade calculada
  - telefone
  - endereco anterior

## Tarefa 4 - Entregas de cestas
- Melhorado o feedback na inclusao manual quando houver:
  - duplicidade no evento
  - bloqueio mensal
  - excesso no limite de cestas
- O bloqueio mensal passou a considerar somente registros com status `retirou`.
- As mensagens agora informam evento anterior, data, senha e quantidade retirada quando houver bloqueio mensal.
- A tela do evento passou a mostrar as regras operacionais do bloqueio mensal e do limite de cestas.

## Tarefa 5 - Equipamentos e emprestimos
- Adicionados novos tipos de equipamento:
  - `cadeira_banho`
  - `equipamentos_enfermaria`
  - `bengala_quatro_pes`
  - `bota_ortopedica_dortler`
  - `tipoia`
- Emprestimo agora registra snapshot de retirada:
  - nome do responsavel
  - telefone
  - CPF
  - endereco
  - nome do usuario do equipamento
- Na devolucao:
  - `bom` e `regular` retornam o item para `disponivel`
  - `ruim` marca o item como `inativo`
  - `ruim` exige descrever a manutencao necessaria
- Foi criado fluxo de retorno apos manutencao para liberar novamente o equipamento.
- Estoque e lista de emprestimos passaram a destacar visualmente itens com manutencao pendente.

## Tarefa 6 - Documentacao
- Atualizado o `README.md` principal com o novo comportamento operacional.
- Registrado este relatorio consolidado em `docs/`.

## Observacoes de verificacao
- Validacao feita com `php -l` nos arquivos alterados.
- Nao foi possivel validar o banco MySQL local em execucao antes da correcao de entregas, porque a instancia em `127.0.0.1:3307` nao estava acessivel durante a implementacao.
- Arquivos transitorios em `.local/mysql` foram mantidos fora dos commits funcionais.
