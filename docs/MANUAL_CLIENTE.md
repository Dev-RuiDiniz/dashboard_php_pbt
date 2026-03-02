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
- `admin`: acesso total (inclusive gestao de usuarios).
- `voluntario`: gestao de familias, criancas, pessoas, entregas, equipamentos, visitas e leitura de relatorios.
- `pastoral`: leitura geral + gestao de pessoas e visitas.
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
- Gerenciar membros da familia (adicionar/editar/remover).

### 5.3 Criancas
- Listar criancas.
- Cadastrar crianca.
- Editar crianca.
- Excluir crianca.

### 5.4 Pessoas acompanhadas e ficha social
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
- Criancas: `/children`
- Pessoas: `/people`
- Entregas: `/delivery-events`
- Equipamentos: `/equipment`
- Emprestimos: `/equipment-loans`
- Visitas: `/visits`
- Relatorios: `/reports`
- Usuarios (admin): `/users`
- Healthcheck: `/health`
