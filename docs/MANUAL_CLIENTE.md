# Manual do Cliente - Sistema Igreja Social

## 1) Objetivo do sistema
O sistema apoia a operacao social da igreja com controle de familias, criancas, pessoas acompanhadas, entregas, equipamentos, visitas, usuarios e relatorios.

## 2) Acesso inicial (primeiro deploy)
Usuarios seed criados pelo script `database/final_mvp.sql`:

| Perfil | Usuario (e-mail) | Senha inicial |
|---|---|---|
| admin | admin@igrejasocial.local | admin123 |
| voluntario | voluntario@igrejasocial.local | admin123 |
| pastoral | pastoral@igrejasocial.local | admin123 |
| viewer | viewer@igrejasocial.local | admin123 |

Regras obrigatorias:
- Alterar todas as senhas no primeiro acesso.
- Nao reutilizar a senha `admin123` em producao.
- Manter apenas usuarios reais e desativar seeds apos onboarding.

## 3) Perfis e permissoes
- `admin`: acesso total (inclusive gestao de usuarios e modulo de pessoas/fichas sociais com dados de moradores de rua).
- `voluntario`: gestao de familias, criancas, entregas, equipamentos, visitas e leitura de relatorios.
- `pastoral`: leitura geral dos modulos permitidos + gestao de visitas.
- `viewer`: somente consulta (sem cadastros/edicoes).

## 4) Como o sistema funciona (fluxo diario)
1. Entrar em `/login`.
2. Ver painel em `/dashboard` com indicadores gerais.
3. Registrar/atualizar dados dos modulos operacionais.
4. Gerar relatorios (PDF/CSV/Excel).
5. Sair pelo logout.

## 5) Funcoes por modulo

### 5.1 Dashboard
- Visao consolidada do status operacional.
- Atalhos para modulos principais.

### 5.2 Familias
- Listar familias.
- Criar familia.
- Editar familia.
- Excluir familia.
- Abrir detalhes da familia.
- Gerenciar pessoas da familia no detalhe (`/families/show?id=...`) com abas e com a primeira aba `Composicao Familiar`.
- Cadastro rapido no formulario de familia:
  - Numero da familia exibido automaticamente pelo sistema (baseado no ID).
  - Mascaras automaticas em CPF, RG e Telefone.
  - Idade da responsavel calculada em tela a partir da data de nascimento.
  - CPF e RG da responsavel principal sao obrigatorios.
  - Listas suspensas em tipo de moradia, estado civil, escolaridade e situacao profissional.
  - Compatibilidade com valores antigos (legados) em cadastros ja existentes.
  - Indicadores de `Adultos`, `Trabalhadores`, `Criancas` e `Renda familiar total` sao informativos (nao editaveis nesta tela).
- Cadastro unificado de pessoa na aba da familia:
  - O detalhe da familia agora organiza o trabalho em abas:
    - `Composicao Familiar`
    - `Resumo`
    - `Entregas`
    - `Emprestimos`
    - `Visitas/Anotacoes`
    - `Pendencias`
  - Escolher o tipo no fluxo inline: `Principal`, `Membro`, `Dependente` ou `Crianca`.
  - `Principal` atualiza os campos da responsavel na propria familia sem abrir outra tela.
  - `Principal` tambem registra `trabalha` e `renda`.
  - `Membro` e `Dependente` exigem CPF e RG.
  - `Dependente` e salvo no cadastro de membros usando parentesco `Dependente`.
  - `Crianca` aceita CPF e RG opcionais.
  - Idade de principal, membro, dependente e crianca e exibida automaticamente na tela ao informar nascimento.
  - Para crianca, `age_years` e calculado e salvo automaticamente a partir de `birth_date` (sem digitacao manual de idade).
  - Cadastro, edicao e exclusao de membros/dependentes/criancas sem sair da pagina de detalhe.
  - `children_count` atualizado automaticamente com base nos registros de criancas.
  - CPF e unico no sistema (bloqueia duplicidade entre familia, membro/dependente, crianca e pessoa acompanhada).
  - O principal da familia continua nos campos da propria familia (responsavel), sem duplicacao em membros.
  - `family_income_total` considera principal + membros + dependentes.
  - `family_income_average` registra a media per capita da familia.
  - `adults_count` e `workers_count` tambem consideram o responsavel principal quando aplicavel.
- Abas de apoio no detalhe:
  - `Entregas`: historico da familia por evento e acesso rapido para abrir evento com a familia preselecionada.
  - `Emprestimos`: historico de emprestimos e atalho para novo emprestimo com `family_id`.
  - `Visitas/Anotacoes`: historico da familia e atalho para solicitar visita.
  - `Pendencias`: documentacao, visita pendente, status do cadastro e observacoes.

### 5.3 Criancas
- Modulo direto de criancas desativado para cadastro.
- Rotas `/children*` redirecionam para `Familias` com orientacao de uso.

### 5.4 Pessoas acompanhadas e ficha social (somente admin)
- Listar pessoas.
- Cadastrar pessoa acompanhada.
- Editar cadastro.
- Excluir pessoa acompanhada.
- Abrir detalhe completo da pessoa.
- Registrar historico de ficha social.
- Editar/remover ficha social.
- Registrar encaminhamentos (criar/editar/excluir).
- Registrar acompanhamento espiritual (criar/editar/excluir).

### 5.5 Entregas
- Criar evento de entrega.
- Editar evento.
- Excluir evento.
- Abrir evento.
- Adicionar convidados na lista operacional.
- Gerar lista automaticamente.
- Atualizar status da entrega (`nao_veio`, `presente`, `retirou`).
- Remover convidado da lista operacional.
- Fechar e reabrir evento.
- Exportar lista (CSV).
- Imprimir lista do evento.

### 5.6 Equipamentos
- Listar equipamentos.
- Cadastrar equipamento.
- Editar equipamento.
- Excluir equipamento.
- Registrar emprestimo.
- Registrar devolucao.
- Excluir emprestimo.
- Acompanhar atrasos e situacao de estoque.

### 5.7 Visitas
- Listar visitas.
- Solicitar nova visita.
- Editar visita.
- Concluir visita.
- Excluir visita.

### 5.8 Relatorios
- Tela de filtros e consolidado.
- Exportacao PDF.
- Exportacao CSV.
- Exportacao Excel (rotas `/reports/excel` e `/reports/xlsx`).

### 5.9 Usuarios (somente admin)
- Listar usuarios.
- Criar usuario.
- Editar usuario.
- Ativar/desativar usuario.
- Excluir usuario (quando sem vinculos bloqueantes no historico).

### 5.10 Autenticacao e seguranca
- Login e logout.
- Bloqueio temporario por tentativas invalidas de login.
- Recuperacao de senha por token temporario.
- Auditoria de eventos em `audit_logs`.

## 6) Rotina recomendada
- Diario:
  - Ver `Dashboard` e pendencias de `Visitas`.
  - Validar entregas abertas.
  - Conferir devolucoes de equipamentos.
- Semanal:
  - Revisar usuarios ativos/inativos.
  - Revisar eventos sensiveis no log de auditoria.
  - Validar backup e restauracao do banco.

## 7) Regras operacionais importantes
- Nao compartilhar usuario e senha entre pessoas.
- Cada operador deve ter conta propria.
- Fazer backup diario do banco.
- Atualizar o sistema somente com backup valido.

## 8) URLs principais
- Login: `/login`
- Dashboard: `/dashboard`
- Familias: `/families`
- Criancas: cadastro centralizado em `Familias` (`/families/show?id=...`)
- Pessoas (somente admin): `/people`
- Fichas sociais (somente admin): `/social-records`
- Entregas: `/delivery-events`
- Equipamentos: `/equipment`
- Emprestimos: `/equipment-loans`
- Visitas: `/visits`
- Relatorios: `/reports`
- Usuarios (admin): `/users`
- Healthcheck: `/health`
