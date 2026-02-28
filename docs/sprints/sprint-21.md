# Sprint 21 - Integracao de CEP nos cadastros

## Entregas
- Integracao de busca de endereco por CEP nos cadastros com campos de endereco.
- Endpoint interno autenticado para consulta de CEP:
  - `GET /api/cep?cep=...`
- Novo servico de consulta com provedor principal dos Correios (BuscaCEP) e fallback opcional:
  - Correios: `api.correios.com.br/cep/v2`
  - fallback: ViaCEP (habilitavel por configuracao)
- Preenchimento automatico no formulario de familias:
  - logradouro
  - bairro
  - cidade
  - UF
  - complemento (somente quando vazio)
- Feedback visual no formulario para estados de consulta:
  - buscando
  - sucesso
  - erro

## Configuracao
- Novas variaveis de ambiente:
  - `CEP_CORREIOS_BASE_URL`
  - `CEP_CORREIOS_BEARER_TOKEN`
  - `CEP_ENABLE_VIACEP_FALLBACK`
  - `CEP_LOOKUP_TIMEOUT`

## Entregaveis tecnicos
- Backend:
  - `app/Controllers/CepController.php`
  - `app/Services/CepLookupService.php`
  - rota adicionada em `config/routes.php`
  - parametros de configuracao adicionados em `config/app.php`
- Frontend:
  - `public/assets/cep-autofill.js`
  - carga global do script em `app/Views/layouts/app.php`
  - ajuste do campo CEP em `app/Views/families/form.php`
- Setup:
  - atualizacao de `.env.example` com variaveis de integracao CEP
