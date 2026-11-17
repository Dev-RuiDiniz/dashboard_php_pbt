# Fluxograma do sistema

O diagrama mostra o caminho normal de uma pessoa da equipe. Cada acesso protegido passa por autenticação e pela permissão correspondente no servidor.

```mermaid
flowchart TD
    A[Entrar no sistema] --> B{Login válido?}
    B -->|Não| C[Mostrar erro e contar tentativa]
    C --> A
    B -->|Sim| D[Carregar perfil e permissões]
    D --> E[Dashboard]

    E --> F[Famílias]
    F --> F1{Família existe?}
    F1 -->|Não| F2[Cadastrar família]
    F1 -->|Sim| F3[Abrir detalhe]
    F2 --> F3
    F3 --> F4[Editar principal, membros e crianças]
    F4 --> F5[Recalcular idade, renda e contagens]
    F3 --> F6[Consultar resumo, entregas, empréstimos, visitas e pendências]

    E --> G{Perfil administrador?}
    G -->|Sim| H[PROJETO AMOR e fichas sociais]
    H --> H1[Manter pessoa acompanhada]
    H1 --> H2[Registrar ficha com consentimento]
    H2 --> H3[Registrar encaminhamentos e acompanhamento espiritual]
    G -->|Não| H4[Essas rotas ficam bloqueadas]

    E --> I[Eventos de entrega]
    I --> I1[Criar ou abrir evento]
    I1 --> I2[Incluir famílias/pessoas ou gerar lista]
    I2 --> I3{Passou por duplicidade, bloqueio mensal e limite?}
    I3 -->|Não| I4[Mostrar motivo e corrigir lista]
    I4 --> I2
    I3 -->|Sim| I5[Gerar senha e operar lista]
    I5 --> I6[Não veio → Presente → Retirou]
    I6 --> I7[Fechar evento, exportar ou imprimir]

    E --> J[Equipamentos]
    J --> J1[Cadastrar e consultar estoque]
    J1 --> J2[Registrar empréstimo, retirante e usuário]
    J2 --> J3{Devolução em boas condições?}
    J3 -->|Sim| J4[Liberar equipamento]
    J3 -->|Não| J5[Marcar inativo e registrar manutenção]
    J5 --> J6[Concluir reparo e liberar novamente]

    E --> K[Visitas]
    K --> K1[Solicitar e agendar]
    K1 --> K2[Realizar visita]
    K2 --> K3[Registrar conclusão e observações]

    E --> L[Relatórios]
    L --> L1[Filtrar período e critérios disponíveis]
    L1 --> L2[Consultar painel ou exportar PDF, CSV e Excel]

    E --> M[Sair]
```

## Em palavras simples

1. A pessoa entra com seu usuário; o sistema verifica quem ela é e o que pode acessar.
2. A equipe registra a família ou pessoa uma vez e acompanha as mudanças dentro do próprio cadastro.
3. Entregas, empréstimos e visitas ficam vinculados ao registro correspondente.
4. O painel e os relatórios resumem o que foi registrado; não substituem a avaliação da equipe.
5. O administrador mantém usuários e acessa as áreas sociais restritas.
