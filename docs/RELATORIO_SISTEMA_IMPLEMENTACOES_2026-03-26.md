# Relatorio Completo de Implementacoes e Funcionamento do Sistema

## Identificacao
- Sistema: Dashboard PHP PBT / Igreja Social
- Data do relatorio: 26/03/2026
- Objetivo: registrar tudo que foi implementado nesta rodada de correcoes e explicar como cada fluxo passa a funcionar no sistema

## Resumo executivo
Nesta entrega foram concluídas 6 frentes de trabalho no sistema:

1. Renomeacao visual do modulo de pessoas acompanhadas para `PROJETO AMOR`, mantendo as rotas internas.
2. Exibicao de datas de cadastro e atualizacao nos cadastros de familia e pessoa.
3. Ajustes no cadastro de familia, com RG opcional, alerta de documentacao pendente e ampliacao da composicao familiar.
4. Inclusao de campos de saude e beneficios sociais no cadastro base de familia e de PROJETO AMOR.
5. Correcao do fluxo operacional de entregas de cestas, com mensagens mais claras e regra mensal mais precisa.
6. Ampliacao do controle de equipamentos e emprestimos, incluindo responsavel pela retirada, usuario do equipamento, manutencao e retorno apos conserto.

## 1. PROJETO AMOR

### O que foi alterado
- O item visivel da barra lateral deixou de aparecer como `Pessoas` e passou a aparecer como `PROJETO AMOR`.
- O titulo principal da listagem tambem passou a exibir `PROJETO AMOR`.
- O texto interno `Pessoas Acompanhadas` dentro do formulario `Nova Pessoa` foi mantido como estava.

### Como funciona no sistema
- O modulo continua tecnicamente ligado a `/people`, sem quebra de rota, permissao ou integracao.
- Para a usuaria final, a area agora reflete o nome correto da frente social voltada aos moradores de rua.
- Isso reduz ambiguidade operacional e alinha a interface com a nomenclatura usada no atendimento.

## 2. Datas de cadastro e atualizacao

### O que foi alterado
- Passaram a ser exibidos os campos de `Data de cadastro` e `Ultima atualizacao` em familias e em PROJETO AMOR.

### Como funciona no sistema
- Sempre que um cadastro de familia ou pessoa for criado, o sistema mostra a data de insercao.
- Sempre que esse cadastro for alterado, o sistema mostra a data da ultima atualizacao.
- Isso melhora auditoria, acompanhamento do historico e revisao dos registros.

## 3. Cadastro de familia

### O que foi alterado
- O RG deixou de ser obrigatorio para:
  - responsavel da familia
  - membros da familia
  - criancas
- A validacao de formato do RG continua existindo, mas apenas quando o campo vier preenchido.
- Foi incluido alerta especifico de `documentacao pendente`.
- A composicao familiar foi ampliada com novos campos de estudo e trabalho.

### Como funciona no sistema

#### 3.1 RG opcional
- Agora o cadastro pode ser salvo sem RG.
- Se o RG for digitado, o sistema ainda valida o formato informado.
- Isso atende a necessidade operacional de nao bloquear o atendimento quando o documento estiver ausente.

#### 3.2 Alerta de documentacao pendente
- No detalhe da familia, alem do alerta de visita, agora existe destaque para situacoes em que a documentacao esta pendente ou parcial.
- Esse alerta ajuda a equipe a identificar rapidamente quais familias precisam complementar comprovacoes.

#### 3.3 Composicao familiar
- Em `Membros da Familia`:
  - para maiores de idade: agora existe `Estuda?` e `Trabalha?`
  - para menores tratados como criancas: existe `Estuda?`
- Isso permite compor melhor a analise da renda per capita e da situacao social do nucleo familiar.

#### 3.4 Correcao de cadastro de crianca
- O fluxo de composicao foi ajustado para facilitar a correcao quando alguem foi inserido errado como crianca.
- A orientacao operacional passa a ser remover da lista de criancas e recadastrar corretamente como membro da familia quando o registro nao for de crianca.

## 4. Saude e beneficios no cadastro base

### O que foi alterado
Foram criados campos estruturados no cadastro principal, e nao apenas na ficha social, para registrar informacoes essenciais.

### 4.1 No cadastro de familia
Para o responsavel da familia, agora existem os campos:
- Doenca cronica
- Deficiencia fisica
- Qual deficiencia fisica
- Medicacao continua
- Qual medicacao continua
- Beneficio social

### 4.2 No cadastro de PROJETO AMOR
Agora existem os campos:
- Idade calculada automaticamente a partir da data de nascimento
- Numero de telefone
- Endereco anterior
- Doenca cronica
- Deficiencia fisica
- Qual deficiencia fisica
- Medicacao continua
- Qual medicacao continua
- Beneficio social

### Como funciona no sistema
- Esses dados ficam disponiveis diretamente no cadastro principal.
- A ficha social continua existindo para historico e acompanhamento, mas as informacoes criticas deixam de depender apenas dela.
- Isso melhora triagem, visualizacao rapida e consistencia do atendimento.

## 5. Entregas de cestas

### O que foi alterado
- O sistema passou a informar com mais clareza por que uma inclusao manual foi recusada.
- O bloqueio mensal foi refinado para considerar apenas quem realmente ficou com status `retirou`.
- A tela do evento passou a mostrar melhor as regras operacionais de bloqueio e limite de cestas.

### Como funciona no sistema

#### 5.1 Duplicidade no mesmo evento
- Se a familia ou pessoa ja estiver cadastrada naquele evento, o sistema informa isso claramente.

#### 5.2 Bloqueio mensal
- O bloqueio mensal nao considera apenas estar listado em outro evento.
- Ele considera apenas se houve uma retirada efetiva no mesmo mes, isto e, status `retirou`.
- Quando o bloqueio ocorre, a mensagem agora informa:
  - nome do evento anterior
  - data do evento
  - senha/ticket
  - quantidade retirada
  - horario registrado, quando existir

#### 5.3 Limite de cestas
- Se a tentativa exceder o limite configurado no evento, a mensagem passa a informar:
  - limite total
  - quantidade ja reservada
  - quantidade da tentativa atual

#### 5.4 Impacto pratico
- A equipe passa a entender rapidamente por que o sistema bloqueou uma inclusao.
- Isso reduz erro operacional e evita a impressao de que o cadastro "falhou sem motivo".

## 6. Equipamentos hospitalares e emprestimos

### O que foi alterado

#### 6.1 Novos tipos de equipamento
Foram adicionados:
- `cadeira_banho`
- `equipamentos_enfermaria`
- `bengala_quatro_pes`
- `bota_ortopedica_dortler`
- `tipoia`

#### 6.2 Novos dados no emprestimo
Agora o emprestimo registra um snapshot do responsavel que retira o equipamento:
- nome do responsavel
- telefone
- CPF
- endereco
- nome do usuario do equipamento

#### 6.3 Fluxo de devolucao e manutencao
- Se a devolucao for `bom` ou `regular`, o equipamento volta para `disponivel`.
- Se a devolucao for `ruim`, o equipamento passa para `inativo`.
- Quando o estado for `ruim`, o sistema exige observacao do que precisa ser feito.
- Foi criado um fluxo para concluir a manutencao e liberar o equipamento novamente.

### Como funciona no sistema

#### 6.4 Identificacao de quem pegou e quem usa
- O sistema agora separa claramente:
  - quem retirou o equipamento
  - para quem o equipamento sera usado
- Isso resolve o problema operacional em casos como:
  - um adulto retira o item
  - o usuario efetivo e outra pessoa da familia

#### 6.5 Controle de manutencao
- Quando o item retorna ruim:
  - a devolucao e registrada
  - o equipamento fica inativo
  - aparece alerta visual de manutencao
  - a observacao de manutencao fica salva
- Depois do conserto:
  - a voluntaria registra o que foi feito
  - informa o estado final do item
  - o sistema libera o equipamento novamente para `disponivel`

#### 6.6 Impacto pratico
- Isso evita reaproveitar item danificado sem controle.
- Tambem cria um historico basico de manutencao e uma regra clara para retorno ao estoque ativo.

## 7. Documentacao atualizada

### O que foi alterado
- O `README.md` principal foi atualizado com os novos comportamentos operacionais.
- Foi criado registro consolidado das mudancas em `docs/`.

### Como funciona no sistema
- A documentacao passa a refletir com mais precisao:
  - o nome `PROJETO AMOR`
  - os novos campos de cadastro
  - o RG opcional
  - a regra de bloqueio de entregas
  - o novo fluxo de equipamentos e manutencao

## 8. Validacoes realizadas
- Foi executada validacao de sintaxe PHP com `php -l` nos arquivos alterados.
- Os commits foram organizados por tarefa funcional.
- O push final foi realizado para o repositorio remoto.

## 9. Observacao importante de ambiente
- Nao foi possivel validar os dados reais do banco MySQL local na porta `3307` durante a implementacao, porque a instancia nao estava acessivel naquele momento.
- Por isso, a correcao do fluxo de entregas foi feita com base no comportamento do codigo e nas regras de negocio implementadas.

## 10. Conclusao
O sistema ficou mais alinhado com a operacao real da acao social, com melhora em nomenclatura, cadastro, rastreabilidade, entrega de cestas e controle de emprestimos de equipamentos. As alteracoes foram feitas preservando a estrutura atual do projeto e sem trocar as rotas internas ja existentes.
