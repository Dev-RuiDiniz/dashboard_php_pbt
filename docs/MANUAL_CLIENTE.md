# Manual Completo do Sistema Igreja Social

## 1. Para que serve este sistema
Este sistema foi feito para ajudar a igreja a organizar o trabalho social do dia a dia.

Com ele, a equipe consegue:
- cadastrar familias
- cadastrar pessoas acompanhadas no `PROJETO AMOR`
- registrar membros e criancas de uma familia
- controlar entregas de cesta basica
- controlar equipamentos emprestados
- organizar visitas
- consultar relatorios
- administrar usuarios

O objetivo principal e evitar perda de informacao, reduzir retrabalho e deixar tudo mais facil para as voluntarias.

## 2. Quem pode usar cada parte
Existem perfis de acesso no sistema.

### `admin`
Pode usar tudo.

### `voluntario`
Pode usar as areas operacionais do trabalho social, como familias, entregas, equipamentos e visitas.

### `pastoral`
Pode consultar varias informacoes e cuidar da area de visitas.

### `viewer`
Pode apenas visualizar, sem cadastrar ou editar.

## 3. Como entrar no sistema
1. Abra o link do sistema.
2. Digite seu usuario e senha.
3. Clique em `Entrar`.

Se a senha estiver errada varias vezes, o sistema pode bloquear temporariamente por seguranca.

## 4. O que voce vai ver no menu
As principais telas do sistema sao:

- `Dashboard`
- `Familias`
- `PROJETO AMOR`
- `Entregas`
- `Equipamentos`
- `Emprestimos`
- `Visitas`
- `Relatorios`
- `Usuarios` (somente admin)

## 5. Tela inicial: Dashboard
O `Dashboard` mostra um resumo rapido da operacao.

Nele voce encontra:
- indicadores gerais
- atalhos para telas principais
- visao rapida do que esta pendente

Use essa tela para ter uma nocao do movimento antes de comecar o atendimento.

## 6. Modulo de Familias
Esta e uma das areas mais usadas do sistema.

### 6.1 O que da para fazer em `Familias`
- cadastrar uma nova familia
- editar uma familia ja existente
- abrir o detalhe da familia
- ver resumo da familia
- registrar membros da casa
- registrar criancas
- ver entregas, visitas, emprestimos e pendencias daquela familia

### 6.2 Como cadastrar uma nova familia
1. Entre em `Familias`.
2. Clique em `Nova Familia`.
3. Preencha os dados principais.
4. Clique em salvar.

### 6.3 Campos principais do cadastro de familia
Os campos mais importantes sao:
- nome da responsavel
- data de nascimento
- CPF
- RG
- telefone
- endereco
- tipo de moradia
- escolaridade
- estado civil
- situacao profissional
- renda
- informacoes de saude

### 6.4 Mudanca importante: CPF nao e mais obrigatorio
Agora o sistema permite salvar a familia mesmo sem CPF.

Funciona assim:
- se o CPF nao for informado, o cadastro pode ser salvo normalmente
- se o CPF for informado, o sistema valida o numero
- se o CPF informado ja existir em outro cadastro relacionado, o sistema avisa duplicidade

Isso foi feito para respeitar situacoes em que a pessoa nao quer ou nao pode informar o CPF.

### 6.5 Mudanca importante: busca por CPF com ou sem mascara
Agora voce pode pesquisar CPF de dois jeitos:
- com pontos e traco
- so com os numeros

Exemplos:
- `123.456.789-00`
- `12345678900`

Os dois formatos funcionam na busca.

### 6.6 Mudanca importante: telefone com mascara
O telefone voltou a aparecer formatado na digitacao e na edicao.

Exemplo:
- `(11) 99999-9999`

Isso ajuda a equipe a visualizar melhor o numero e evita erro de digitacao.

### 6.7 Mudanca importante: novos campos de saude
No cadastro de familia agora existem estes campos:

- `Faz uso de medicacao continua?`
- `Tem algum vicio?`
- `Qual?`

Como funciona:
- se marcar que `nao`, o campo `Qual?` nao precisa ser preenchido
- se marcar que `sim`, o campo `Qual?` aparece e deve ser informado

### 6.8 Mudanca importante: doenca cronica agora aceita varias opcoes
Antes o campo aceitava apenas uma opcao.
Agora e possivel marcar varias.

As opcoes incluem:
- hipertensao
- diabetes
- doencas cardiovasculares
- obesidade
- doenca osteomuscular
- depressao
- transtornos mentais
- problemas com tireoide
- problemas de varizes
- colesterol alto
- coluna vertebral
- nervo ciatico
- problemas com visao
- outra

Se marcar `Outra`, o campo `Qual?` aparece e deve ser preenchido.

### 6.9 Mudanca importante: tipo de moradia com campo complementar
No campo `Tipo de moradia`, se voce escolher `Outro`, o sistema abre o campo `Qual?`.

Esse campo serve para explicar qual e o outro tipo de moradia.

### 6.10 Mudanca importante: links para consulta de CEP
Perto do campo `CEP`, agora existem links rapidos para consulta externa.

Esses links abrem em nova aba e ajudam quando a voluntaria nao sabe o CEP correto.

### 6.11 O que existe dentro do detalhe da familia
Quando voce abre uma familia, aparecem abas com informacoes organizadas.

As abas principais sao:
- `Composicao Familiar`
- `Resumo`
- `Entregas`
- `Emprestimos`
- `Visitas/Anotacoes`
- `Pendencias`

## 7. Composicao Familiar
Essa parte serve para registrar quem mora na mesma casa.

### 7.1 O que pode ser registrado
- responsavel principal
- membro da familia
- crianca

### 7.2 Mudanca importante: novos campos por membro
Agora cada membro da familia pode ter:
- `Recebe algum beneficio social?`
- `Trabalha?`
- `Objetivo`

Esses campos foram criados para melhorar a visao da renda real da casa e da situacao de cada pessoa.

### 7.3 Campo `Objetivo`
Esse campo pode ser usado para registrar algo importante sobre aquele membro.

Exemplos:
- procurar emprego
- concluir estudos
- tratamento de saude
- acompanhamento social

### 7.4 Mudanca importante: parentesco de crianca
Foi corrigido o problema em que a lista de `Parentesco` nao aparecia no cadastro de crianca.

Agora a lista aparece normalmente tambem para crianca.

### 7.5 Mudanca importante: renda com salvamento mais claro
O sistema foi ajustado para reduzir perda de informacao na renda.

Agora:
- a renda fica ligada ao salvamento do proprio bloco da pessoa
- ao salvar principal ou membro, a mensagem deixa claro que a renda foi salva

Importante:
- se a pessoa preencher e nao salvar o bloco, a informacao ainda nao foi gravada
- por isso sempre clique em salvar naquele formulario antes de sair da tela

### 7.6 Resumo da renda familiar
O sistema calcula automaticamente:
- renda total da familia
- media de renda per capita
- quantidade de adultos
- quantidade de trabalhadores
- quantidade de criancas

## 8. Aba Resumo da Familia
Na aba `Resumo`, voce encontra uma visao geral da familia.

Ali aparecem:
- dados pessoais da responsavel
- moradia
- renda
- saude
- beneficio social
- informacoes complementares

As novas informacoes de `vicio`, `doenca cronica` e `tipo de moradia outro` tambem aparecem nessa tela.

## 9. PROJETO AMOR
Essa area e usada para acompanhar pessoas atendidas fora do cadastro de familia tradicional.

### 9.1 O que da para fazer
- cadastrar pessoa acompanhada
- editar cadastro
- consultar historico
- registrar ficha social
- registrar encaminhamentos
- registrar acompanhamento espiritual

### 9.2 Mudancas importantes no PROJETO AMOR
O modulo recebeu as mesmas melhorias principais de `Familias`:
- CPF opcional
- busca por CPF com ou sem mascara
- telefone com mascara
- campo `Tem algum vicio?`
- campo `Qual?` para vicio
- `Doenca cronica` com varias opcoes
- `Outra` com campo `Qual?`

### 9.3 Como funciona o CPF aqui
Assim como em `Familias`:
- o CPF nao e obrigatorio
- se for informado, ele sera validado
- se ja existir em modulo relacionado, o sistema avisa

## 10. Ficha Social no PROJETO AMOR
Dentro do detalhe da pessoa, existe a area de ficha social.

Ela ajuda a registrar:
- necessidades imediatas
- observacoes de saude
- uso de medicacao
- aceite de visita
- pedido de oracao
- igreja
- observacoes do atendimento

Essa parte registra data e hora do consentimento automaticamente.

## 11. Encaminhamentos
Na pessoa acompanhada, tambem e possivel registrar encaminhamentos.

Exemplos:
- CRAS
- CAPS
- UBS
- assistencia juridica

Voce pode:
- criar
- editar
- filtrar
- remover

## 12. Acompanhamento espiritual
Tambem dentro da pessoa acompanhada, da para registrar:
- visita
- oracao
- aconselhamento
- outras acoes espirituais

## 13. Entregas de cesta basica
Essa area controla os eventos de entrega e a lista de convidados.

### 13.1 O que da para fazer
- criar evento
- editar evento
- gerar lista manual
- gerar lista automatica
- marcar status da entrega
- exportar CSV
- imprimir lista
- concluir ou reabrir evento

### 13.2 Status possiveis
Os status principais na lista sao:
- `nao_veio`
- `presente`
- `retirou`

### 13.3 Bloqueio mensal
O sistema pode bloquear entregas repetidas no mesmo mes para a mesma familia ou pessoa.

Isso continua existindo como protecao padrao.

### 13.4 Mudanca importante: excecao controlada no bloqueio mensal
Agora existe uma liberacao excepcional, mas com controle.

Como funciona:
- a regra padrao continua valendo
- somente `admin` pode liberar a excecao
- a excecao e manual
- a justificativa e obrigatoria
- a justificativa fica gravada na propria entrega

Isso permite atender casos autorizados sem remover a protecao do sistema.

### 13.5 Exemplo pratico
Se uma familia ja retirou cesta no mes, o sistema normalmente bloqueia nova retirada.

Mas se houver autorizacao especial:
1. o `admin` marca a opcao de entrega excepcional
2. preenche a justificativa
3. salva a entrega

Assim fica registrado quem autorizou e por que houve a excecao.

## 14. Equipamentos
Essa area serve para controlar os equipamentos sociais.

### 14.1 O que da para fazer
- cadastrar equipamento
- editar equipamento
- excluir equipamento
- ver situacao

### 14.2 Emprestimos
Tambem e possivel:
- registrar retirada
- registrar devolucao
- acompanhar atrasos
- ver historico

## 15. Visitas
Na area de `Visitas`, a equipe pode:
- solicitar visita
- editar visita
- concluir visita
- excluir visita
- acompanhar observacoes

## 16. Relatorios
Os relatorios ajudam a consultar e exportar informacoes.

Normalmente a equipe usa essa area para:
- filtrar dados
- gerar PDF
- gerar CSV
- gerar Excel

## 17. Usuarios
Essa area e apenas para `admin`.

Aqui o administrador pode:
- criar usuarios
- editar usuarios
- ativar ou desativar acessos
- excluir usuarios quando permitido

## 18. Regras simples para evitar erros
Para usar o sistema com seguranca, siga estas orientacoes:

- sempre clique em `Salvar` antes de sair da tela
- nao compartilhe usuario e senha
- confira o CPF quando ele for informado
- use a busca por nome ou CPF para evitar cadastro duplicado
- em caso de excecao de cesta, registre sempre a justificativa correta
- mantenha os telefones atualizados
- preencha o maximo possivel dos campos de saude quando a pessoa aceitar informar

## 19. Como trabalhar no dia a dia
Uma rotina simples recomendada e:

1. entrar no sistema
2. olhar o `Dashboard`
3. consultar `Familias` ou `PROJETO AMOR`
4. atualizar cadastros
5. registrar entregas, visitas ou emprestimos quando necessario
6. tirar relatorios
7. sair do sistema

## 20. Resumo das mudancas mais recentes
As ultimas melhorias importantes foram:

- CPF deixou de ser obrigatorio em `Familias`
- busca por CPF agora funciona com e sem mascara
- telefone voltou a ficar formatado na digitacao e na edicao
- `Tem algum vicio?` e `Qual?` foram adicionados em `Familias` e `PROJETO AMOR`
- `Doenca cronica` agora aceita varias opcoes
- `Outra` em doenca cronica abre o campo `Qual?`
- `Outro` em tipo de moradia abre o campo `Qual?`
- `Composicao Familiar` ganhou os campos `beneficio social`, `trabalha` e `objetivo`
- parentesco da crianca foi corrigido
- bloqueio mensal de cesta agora pode ter excecao controlada por `admin`
- timezone foi ajustado para o horario do Brasil usado pela operacao

## 21. O que fazer se algo der errado
Se perceber qualquer problema:

1. anote a tela onde aconteceu
2. anote o nome da pessoa ou familia envolvida
3. se possivel, tire print da tela
4. informe a equipe responsavel pelo sistema

Se o erro envolver entrega, CPF ou renda, informe isso logo no chamado porque sao areas sensiveis para a operacao.

## 22. Enderecos principais do sistema
- `Login`: `/login`
- `Dashboard`: `/dashboard`
- `Familias`: `/families`
- `PROJETO AMOR`: `/people`
- `Entregas`: `/delivery-events`
- `Equipamentos`: `/equipment`
- `Emprestimos`: `/equipment-loans`
- `Visitas`: `/visits`
- `Relatorios`: `/reports`
- `Usuarios`: `/users`

## 23. Mensagem final para a equipe
Este sistema foi pensado para ajudar no cuidado com as pessoas e na organizacao do atendimento.

Nao e preciso decorar tudo de uma vez.
O mais importante e:
- buscar antes de cadastrar
- preencher com calma
- salvar antes de sair
- registrar excecoes com clareza

Com isso, a equipe consegue trabalhar com mais seguranca e menos retrabalho.
