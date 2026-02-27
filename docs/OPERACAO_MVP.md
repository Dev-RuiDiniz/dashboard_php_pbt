# Operacao MVP - Guia rapido

## Usuarios e acesso
- Perfis:
  - `admin`: acesso total.
  - `voluntario`: operacao social e cadastros.
  - `pastoral`: acompanhamento e consulta.
  - `viewer`: somente consulta.

## Fluxos principais
1. Cadastro social:
- `Familias` e `Criancas`.
- `Pessoas acompanhadas` + ficha social.

2. Entregas:
- Criar `Evento de entrega`.
- Gerar lista operacional.
- Atualizar fluxo: `nao_veio -> presente -> retirou`.

3. Equipamentos:
- Cadastro de estoque.
- Emprestimo e devolucao.
- Acompanhar atrasos de devolucao.

4. Visitas:
- Solicitar visita.
- Agendar/concluir.
- Monitorar pendencias no painel de visitas.

5. Relatorios:
- Definir periodo/status/bairro.
- Gerar visao mensal.
- Exportar PDF.

## Seguranca operacional
- Bloqueio por tentativas de login ativado.
- Recuperacao de senha por token temporario.
- Eventos relevantes gravados em `audit_logs`.

## Rotina diaria recomendada
- Iniciar pelo modulo `Visitas` para pendencias.
- Conferir `Entregas` (se houver evento aberto).
- Validar `Equipamentos` com devolucao atrasada.
- Fechar com `Relatorios` para acompanhamento mensal.

## Rotina semanal recomendada
- Revisar usuarios inativos/duplicados.
- Auditar eventos sensiveis no log.
- Conferir backup do banco e integridade de restore.
