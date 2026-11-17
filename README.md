# Sistema Igreja Social

O Sistema Igreja Social é uma aplicação web para a equipe organizar o atendimento social da igreja. Reúne cadastros de famílias e pessoas acompanhadas, entregas, empréstimos de equipamentos, visitas e relatórios em um só lugar.

## O que ajuda a equipe a fazer

- Manter o cadastro e o histórico de cada família, sua composição, contatos e pendências.
- Registrar pessoas acompanhadas, atendimentos sociais, encaminhamentos e acompanhamento espiritual.
- Preparar listas de entrega e acompanhar quem compareceu e quem retirou.
- Controlar equipamentos disponíveis, emprestados, atrasados e aguardando manutenção.
- Solicitar visitas e acompanhar o que ainda está pendente.
- Consultar indicadores e gerar relatórios para apoiar o acompanhamento da operação.

## Como é usado

1. A equipe entra com uma conta individual.
2. Registra ou localiza a família ou pessoa atendida.
3. Mantém o histórico de entregas, visitas e equipamentos ligado ao atendimento.
4. Acompanha pendências no painel e consulta os relatórios.

O acesso a cada área depende do perfil do usuário. A área `PROJETO AMOR` e as fichas sociais são restritas a administradores.

## Módulos

- **Dashboard:** indicadores e alertas da operação.
- **Famílias:** cadastro, telefones, composição familiar, resumo, entregas, empréstimos, visitas e pendências.
- **Crianças:** cadastro e acompanhamento dentro do detalhe da família.
- **PROJETO AMOR:** pessoas acompanhadas, disponível para administradores.
- **Fichas Sociais:** atendimentos, consentimento, encaminhamentos e acompanhamento espiritual, disponível para administradores.
- **Entregas:** eventos, lista operacional, controle de retirada, exportação e impressão.
- **Equipamentos:** estoque, empréstimos, devoluções e manutenção.
- **Visitas:** solicitações, agendamentos, conclusão e observações.
- **Relatórios:** consulta por filtros e exportação em PDF, CSV ou formato compatível com Excel.
- **Usuários:** administração de contas e perfis.

## Abrir a versão local

O projeto já está configurado para Docker Desktop. No PowerShell, dentro desta pasta, execute:

```powershell
docker compose -p dashboard-php-pbt-local up -d --build
```

Depois abra [http://localhost:8093/login](http://localhost:8093/login). O banco local começa com o schema e os dados de exemplo de `database/final_mvp.sql`; ele não usa uma cópia do banco de produção.

Para parar os containers mantendo os dados locais:

```powershell
docker compose -p dashboard-php-pbt-local down
```

As credenciais de desenvolvimento estão em `ACESSOS.md`, um arquivo local ignorado pelo Git e pelo Docker. Não use as contas de exemplo em produção.

## Documentos do projeto

- [PRD e regras de negócio](PRD.md)
- [Roadmap](ROADMAP.md)
- [Fluxograma](FLUXOGRAMA.md)
- [Regras de execução do repositório](AGENTS.md)
- [Histórico de sprints e operação](docs/README.md)
- [Guia de operação](docs/OPERACAO_MVP.md)
- [Checklist de publicação Hostinger](docs/DEPLOY_HOSTINGER_CHECKLIST.md)

## Tecnologia

PHP 8.2+, MySQL 8/MariaDB, Composer, PDO, Bootstrap e Apache. A aplicação segue uma arquitetura MVC simples e usa rotas PHP server-side.
