# PRD — Sistema Igreja Social

**Atualizado em:** 17/11/2026
**Status:** MVP operacional, com correções recentes ainda em validação local.

Este documento descreve as regras do produto que estão implementadas no código atual. O Word original e os relatórios antigos registram decisões de etapas anteriores; quando houver diferença, prevalecem o código, as migrations mais recentes e este PRD atualizado.

## 1. Objetivo

Centralizar o trabalho social da igreja em uma aplicação web: registrar famílias e pessoas acompanhadas, organizar entregas e empréstimos, acompanhar visitas e produzir indicadores. O sistema substitui controles espalhados e ajuda a equipe a localizar o histórico de cada atendimento.

## 2. Usuários e permissões

| Perfil | Acesso atual |
| --- | --- |
| `admin` | Acesso total, incluindo usuários, `PROJETO AMOR`, fichas sociais e auditoria. |
| `voluntario` | Gerencia famílias, entregas, equipamentos e visitas; consulta relatórios. |
| `pastoral` | Consulta famílias, entregas, equipamentos e relatórios; gerencia visitas. |
| `viewer` | Consulta famílias, entregas, equipamentos, visitas e relatórios. |

As rotas de `PROJETO AMOR` (`/people`) e `Fichas Sociais` (`/social-records`) são restritas a administradores. A permissão é conferida no servidor, além de o menu ser ocultado para os demais perfis.

## 3. Funcionalidades

### 3.1 Famílias e composição

- Cadastrar, consultar, editar e desativar famílias.
- Registrar responsável, documentos, telefones, endereço, renda, moradia, saúde, benefícios e pendências.
- Manter vários telefones, com identificação e seleção do principal.
- Registrar composição na própria família: responsável principal, membros, dependentes e crianças.
- Consultar abas de composição, resumo, entregas, empréstimos, visitas/anotações e pendências.
- Ver idade calculada a partir do nascimento e indicadores derivados da composição: adultos, trabalhadores, crianças, renda total e média per capita.
- Cadastrar e editar crianças dentro do detalhe da família. O antigo módulo `/children` redireciona para famílias.

### 3.2 `PROJETO AMOR` e fichas sociais

- Área administrativa para pessoas acompanhadas, inclusive pessoas em situação de rua.
- Registrar dados de identificação, telefones, saúde, necessidades, histórico de atendimento, encaminhamentos e acompanhamento espiritual.
- Vincular opcionalmente uma ficha social a uma família.
- Registrar consentimento digital por nome e versão do termo; ambos são obrigatórios ao salvar uma ficha social e a data/hora fica registrada.

### 3.3 Entregas

- Criar e administrar eventos; incluir famílias ou pessoas manualmente ou gerar listas por critérios operacionais.
- Configurar bloqueio mensal e limite de cestas por evento.
- Acompanhar a lista por senha e status, exportar CSV, imprimir e fechar/reabrir eventos.
- O fluxo de cada registro é `nao_veio` → `presente` → `retirou`. A retirada registra quantidade, horário, operador e nome digitado para confirmação.
- Quando o bloqueio mensal estiver ativo, a validação considera uma retirada efetiva (`retirou`), não apenas a presença em outra lista.
- Um evento não pode ser concluído enquanto houver pessoas marcadas como presentes aguardando retirada.

### 3.4 Equipamentos e empréstimos

- Cadastrar equipamentos, consultar disponibilidade, registrar empréstimo, devolução e manutenção.
- O empréstimo guarda dados de quem retirou e de quem usará o equipamento, além de telefone, CPF e endereço quando fornecidos.
- Uma devolução em boas condições libera o equipamento; retorno em condição ruim exige descrição da manutenção e deixa o equipamento inativo até o registro de conclusão do reparo.
- Empréstimos vencidos e equipamentos em manutenção aparecem nos indicadores operacionais.

### 3.5 Visitas, painel e relatórios

- Solicitar, agendar, concluir e consultar visitas ligadas a famílias ou pessoas.
- Exibir pendências de documentação, visitas, devoluções atrasadas e cadastros sem atualização recente.
- Consultar indicadores do painel e relatórios filtrados por período e outros critérios disponíveis.
- Exportar relatórios em PDF, CSV e formato compatível com Excel.

## 4. Regras gerais de dados

1. O nome da pessoa responsável pela família é obrigatório.
2. CPF e RG podem ficar vazios quando não estiverem disponíveis. Quando informados, seus formatos são validados; CPF não pode se repetir entre família, membro, criança e pessoa acompanhada.
3. Informações de aluguel aparecem quando a moradia é `Alugada`; o valor é salvo quando informado. O backend não exige valor de aluguel no estado atual.
4. Ao selecionar doença crônica `Outra`, o detalhe correspondente é obrigatório ao salvar.
5. Ao selecionar moradia `Outro`, o detalhe do tipo é obrigatório.
6. Renda familiar, idade e contagens são derivadas dos registros de composição; não devem ser editadas como números independentes.
7. Dados de saúde, documentos e situação social são sensíveis. O acesso deve respeitar perfil e as fichas sociais devem manter o consentimento registrado.
8. Senhas de usuários são armazenadas como hash. Consultas ao banco devem usar PDO e parâmetros preparados.

## 5. Requisitos das solicitações recentes do cliente

As correções estão no workspace local e ainda não foram publicadas. O aceite funcional esperado é:

1. **Telefones:** adicionar, remover, marcar o principal e salvar a lista atualizada em famílias, pessoas e responsável principal. Também deve ser possível remover todos os números.
2. **Moradia alugada:** selecionar `Alugada` mostra imediatamente o campo de valor; o campo permanece editável antes do salvamento.
3. **Doença crônica — Outra:** marcar `Outra` mostra imediatamente `Qual?`; o detalhe informado é preservado ao salvar.
4. **Empréstimos:** a visualização mostra o nome salvo de quem retirou. Se esse campo estiver vazio, usa a pessoa ou família vinculada como alternativa; sem nome nem vínculo, o cadastro precisa ser corrigido.

## 6. Limites do produto

- É uma ferramenta interna de operação e acompanhamento social, não um portal público para autoatendimento.
- A tela de retirada registra uma confirmação nominal digitada; não é assinatura eletrônica certificada.
- O sistema oferece registros e indicadores operacionais; decisões de atendimento continuam sob responsabilidade da equipe.

## 7. Referências

- [Histórico das sprints](docs/README.md)
- [Guia de operação](docs/OPERACAO_MVP.md)
- [Regras do repositório](AGENTS.md)
- [Fluxo do sistema](FLUXOGRAMA.md)
- [Plano de execução](ROADMAP.md)
