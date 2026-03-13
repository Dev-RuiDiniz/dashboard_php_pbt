# Teste Integrado - Fluxos principais (MVP)

## Objetivo
Validar ponta a ponta os fluxos criticos antes/depois do deploy.

## Pre-condicoes
- Banco migrado e seed aplicado.
- Usuario admin disponivel.
- `.env` configurado para o ambiente alvo.

## Cenarios
1. Autenticacao e seguranca
- [ ] Login com credencial valida.
- [ ] Login invalido repetido ate bloqueio.
- [ ] Recuperacao de senha com token e novo login.

2. Cadastros sociais
- [ ] Criar familia.
- [ ] Confirmar mascara em CPF, RG e Telefone durante digitacao na familia.
- [ ] Confirmar listas suspensas de moradia, estado civil, escolaridade e situacao profissional.
- [ ] Confirmar idade calculada da responsavel ao informar nascimento.
- [ ] Confirmar exibicao do numero da familia na listagem, formulario (edicao) e detalhe.
- [ ] Na pagina de detalhe da familia, clicar em `Adicionar pessoa` e alternar entre `Principal`, `Membro`, `Dependente` e `Crianca`.
- [ ] Atualizar dados do principal na propria aba da familia e validar persistencia.
- [ ] Cadastrar membro pelo fluxo unificado e validar persistencia na mesma pagina.
- [ ] Cadastrar dependente pelo fluxo unificado e validar gravacao em `family_members` com parentesco `Dependente`.
- [ ] Cadastrar crianca na aba da familia (sem usar modulo `/children`).
- [ ] Editar/remover membro, dependente e crianca na mesma pagina da familia.
- [ ] Confirmar `children_count` automatico apos criar/editar/remover crianca.
- [ ] Confirmar redirecionamento informativo das rotas `/children*` para `Familias`.
- [ ] Confirmar que `Adultos`, `Trabalhadores`, `Criancas` e `Renda familiar total` nao aceitam edicao manual no formulario de familia.
- [ ] Incluir membro/dependente e validar recalculo automatico de `family_income_total`, `adults_count` e `workers_count`.
- [ ] Alterar/remover membro/dependente e validar novo recalculo automatico.
- [ ] Validar CPF/RG obrigatorios para principal, membro e dependente.
- [ ] Validar crianca com CPF/RG opcionais e validacao de formato quando preenchidos.
- [ ] Validar bloqueio de CPF duplicado entre `families`, `family_members`, `children` e `people`.
- [ ] Confirmar idade automatica em tela para principal, membro/dependente e crianca ao alterar nascimento.
- [ ] Confirmar persistencia automatica de `children.age_years` via `birth_date` (sem campo manual de idade).
- [ ] Cadastrar pessoa acompanhada + ficha social.

3. Encaminhamentos
- [ ] Criar encaminhamento por atendimento.
- [ ] Alterar status do encaminhamento.

4. Entregas
- [ ] Criar evento de entrega.
- [ ] Adicionar convidado familia/pessoa.
- [ ] Atualizar status no fluxo permitido.
- [ ] Validar bloqueios mensais e limites de cestas.

5. Equipamentos
- [ ] Cadastrar equipamento.
- [ ] Registrar emprestimo e verificar status `emprestado`.
- [ ] Registrar devolucao e verificar status `disponivel`.
- [ ] Confirmar alerta de atraso (quando aplicavel).

6. Visitas e pendencias
- [ ] Solicitar visita para familia/pessoa.
- [ ] Concluir visita.
- [ ] Confirmar cards/listas de pendencias.

7. Relatorios
- [ ] Filtrar por periodo/status/bairro.
- [ ] Conferir agregados de familias/cestas/criancas/encaminhamentos.
- [ ] Exportar PDF com sucesso.

8. Auditoria
- [ ] Confirmar registros em `audit_logs` para:
  - login/logout
  - alteracao de usuario
  - operacoes de visitas
  - visualizacao/exportacao de relatorios

## Resultado
- [ ] Aprovado para producao
- [ ] Reprovado (registrar ajustes)
