# Roadmap — Sistema Igreja Social

**Atualizado em:** 17/11/2026
**Base:** código local derivado da VPS, migrations e histórico documentado até a Sprint 37.

O roadmap separa o que já está registrado como entregue do que ainda precisa de validação ou decisão. Não atribui prazos sem aprovação do cliente.

## Concluído e documentado

- **Sprints 1–20 — MVP:** estrutura PHP/MVC, autenticação e perfis, famílias e pessoas, fichas sociais, entregas, equipamentos, visitas, relatórios, auditoria e publicação inicial.
- **Sprints 21–31 — operação:** consulta de CEP, identidade visual, painel com dados reais, relatórios/exportações, filtros e fechamento de eventos, responsividade, restrição de áreas sensíveis e ajustes operacionais.
- **Sprints 32–37 — famílias:** cadastro rápido, crianças centralizadas no detalhe da família, composição unificada, documentos e telefones, cálculos automáticos, abas e resumo per capita.
- **Pacote de 26/03/2026:** dados de saúde e benefícios, regras de retirada, manutenção de equipamentos e valor de aluguel.

Os detalhes estão em [`docs/sprints/`](docs/sprints/) e nos relatórios em [`docs/`](docs/).

## Em validação local

As quatro solicitações recentes foram ajustadas no código local, mas ainda precisam de conferência funcional e aceite do cliente:

- [ ] Adicionar/remover telefones em família, pessoa e responsável principal; validar salvamento do principal e remoção de todos.
- [ ] Selecionar `Alugada` e confirmar que o campo de valor aparece antes de salvar.
- [ ] Selecionar doença crônica `Outra` e confirmar que `Qual?` aparece e preserva o conteúdo.
- [ ] Conferir nomes de retirantes em empréstimos com vínculo familiar/pessoa e identificar cadastros antigos sem qualquer nome ou vínculo.

## Próximos passos

### 1. Aceite funcional

- [ ] Reproduzir os quatro fluxos no Docker local com registros de exemplo.
- [ ] Registrar resultado esperado, resultado observado e evidência para cada solicitação.
- [ ] Ajustar os casos que não passarem e obter aceite do cliente antes da publicação.

### 2. Preparação de release

- [ ] Atualizar o roteiro integrado para incluir os quatro critérios novos.
- [ ] Conferir migrations e schema entre a versão local, GitHub e VPS antes de publicar.
- [ ] Gerar backup do banco de produção e confirmar que pode ser restaurado.
- [ ] Publicar em janela combinada, executar smoke test e conferir `/health` e logs.
- [ ] Registrar a versão publicada e manter um plano de retorno à versão anterior.

### 3. Operação e segurança

- [ ] Atualizar o checklist de Hostinger, que contém verificações antigas de março de 2026, com o estado atual da VPS.
- [ ] Confirmar que usuários seed foram substituídos ou desativados em produção e que cada operador tem conta própria.
- [ ] Confirmar rotina e retenção de backup com a equipe responsável.
- [ ] Revisar periodicamente permissões de `PROJETO AMOR`, exportações e dados sensíveis.

## Fora do plano aprovado

Novos módulos, integrações e automações não descritos no PRD devem entrar no backlog somente após definição do problema, responsável e critério de aceite pelo cliente.
