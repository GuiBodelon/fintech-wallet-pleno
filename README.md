# Fintech Wallet Pleno

MVP fullstack de carteira digital pessoal desenvolvido para um desafio técnico. A aplicação permite cadastro e login de usuários, consulta de saldo, depósitos, saques, histórico paginado de transações e dashboard com resumo mensal.

## Links

| Item | Link |
|---|---|
| Repositório público | https://github.com/GuiBodelon/fintech-wallet-pleno |
| Deploy público | https://fintech-wallet-web-production.up.railway.app/ |
| Backend API | https://fintech-wallet-api-production.up.railway.app/api |

## Stack

**Backend**

- PHP 8.4+
- Laravel 13
- Laravel Sanctum
- PostgreSQL
- Eloquent ORM
- Form Requests
- Service Layer para regras financeiras
- PHPUnit

**Frontend**

- Node.js 22
- Nuxt 3 em modo SPA
- Vue 3 com Composition API
- TypeScript
- Pinia
- TailwindCSS
- Nuxt UI
- pnpm

**Infra**

- Docker Compose
- PostgreSQL 17 Alpine

## Pré-requisitos

Para rodar com Docker:

- Docker
- Docker Compose

Para rodar comandos fora do Docker:

- PHP 8.4+
- Composer 2+
- Node.js 22+
- pnpm 11+
- PostgreSQL 17+ ou outro PostgreSQL compatível

## Configuração do ambiente

Os arquivos `.env` reais não são versionados. Crie os arquivos locais a partir dos exemplos:

```bash
cp .env.example .env
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env
```

Windows PowerShell:

```powershell
Copy-Item .env.example .env
Copy-Item backend/.env.example backend/.env
Copy-Item frontend/.env.example frontend/.env
```

Variáveis importantes:

```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=fintech_wallet
DB_USERNAME=fintech_wallet
DB_PASSWORD=secret

NUXT_PUBLIC_API_BASE_URL=http://localhost:8000/api
```

Ao usar Docker, `DB_HOST` deve ser `postgres`, pois esse é o nome do serviço do banco no `docker-compose.yml`.

## Instalação e execução com Docker

Suba os serviços:

```bash
docker compose up -d --build
```

O container do backend executa `composer install`, gera `APP_KEY` se necessário e roda as migrations. O container do frontend executa `pnpm install` e inicia o Nuxt em modo desenvolvimento.

Depois que os containers estiverem de pé, rode seeders para popular dados de teste:

```bash
docker compose exec backend php artisan migrate:fresh --seed
```

URLs locais:

| Serviço | URL |
|---|---|
| Frontend | http://localhost:3000 |
| Backend API | http://localhost:8000/api |
| PostgreSQL | localhost:5432 |

## Instalação manual de dependências

Backend:

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

Frontend:

```bash
cd frontend
pnpm install
cp .env.example .env
pnpm dev
```

Se o frontend estiver fora do Docker, mantenha em `frontend/.env`:

```env
NUXT_PUBLIC_API_BASE_URL=http://localhost:8000/api
```

## Testes e validação

Backend:

```bash
docker compose exec backend php artisan test
```

Frontend:

```bash
docker compose exec frontend pnpm typecheck
docker compose exec frontend pnpm build
```

Também é possível rodar fora do Docker dentro de `frontend/`:

```bash
pnpm typecheck
pnpm build
```

## Usuários seedados

Depois de rodar `docker compose exec backend php artisan migrate:fresh --seed`, os usuários abaixo ficam disponíveis:

| Usuário | E-mail | Senha | Cenário |
|---|---|---|---|
| Demo User | `demo@fintech.test` | `password` | Usuário principal com saldo positivo e histórico com créditos e débitos |
| Empty Wallet User | `empty@fintech.test` | `password` | Wallet zerada e sem transações |
| Low Balance User | `lowbalance@fintech.test` | `password` | Saldo baixo para testar saque insuficiente |

## Endpoints da API

Endpoints protegidos exigem token Sanctum no header:

```http
Authorization: Bearer <token>
```

Fluxo básico:

1. Faça login em `POST /api/login`.
2. Copie o token retornado em `data.token`.
3. Use o token nas próximas requisições protegidas.

### Auth

| Método | Rota | Autenticação | Descrição |
|---|---|---|---|
| `POST` | `/api/register` | Não | Cadastra usuário e cria wallet com saldo zero |
| `POST` | `/api/login` | Não | Autentica usuário e retorna token Sanctum |
| `POST` | `/api/logout` | Sim | Revoga o token atual |
| `GET` | `/api/me` | Sim | Retorna dados do usuário autenticado |

Payload de cadastro:

```json
{
  "name": "Demo User",
  "email": "demo@fintech.test",
  "password": "password",
  "password_confirmation": "password"
}
```

Payload de login:

```json
{
  "email": "demo@fintech.test",
  "password": "password"
}
```

### Wallet

| Método | Rota | Autenticação | Descrição |
|---|---|---|---|
| `GET` | `/api/wallet` | Sim | Retorna a carteira do usuário autenticado |
| `POST` | `/api/wallet/deposit` | Sim | Realiza depósito |
| `POST` | `/api/wallet/withdraw` | Sim | Realiza saque |

Payload de depósito:

```json
{
  "amount": "250.75"
}
```

Payload de saque:

```json
{
  "amount": "49.90"
}
```

### Transactions

| Método | Rota | Autenticação | Descrição |
|---|---|---|---|
| `GET` | `/api/transactions` | Sim | Lista transações do usuário autenticado com filtros e paginação |

Filtros disponíveis:

```txt
type=credit|debit
from=YYYY-MM-DD
to=YYYY-MM-DD
page=1
per_page=10
```

Exemplo:

```txt
GET /api/transactions?type=credit&from=2026-07-01&to=2026-07-31&page=1&per_page=10
```

### Dashboard

| Método | Rota | Autenticação | Descrição |
|---|---|---|---|
| `GET` | `/api/dashboard` | Sim | Retorna saldo atual, últimas 5 transações e totais do mês atual |

## Padrão de respostas da API

Sucesso:

```json
{
  "success": true,
  "message": "Deposit completed successfully.",
  "data": {}
}
```

Erro de validação:

```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": {
    "amount": ["The amount must be a valid currency value greater than zero."]
  }
}
```

Erro de regra de negócio:

```json
{
  "success": false,
  "message": "Insufficient wallet balance."
}
```

## Collection do Insomnia

A collection está disponível em:

```txt
docs/fintech-wallet-pleno-insomnia-collection.json
```

Para usar:

1. Importe o arquivo JSON no Insomnia.
2. Execute o login com um usuário seedado.
3. Copie o token retornado em `data.token`.
4. Configure a variável `auth_token` com esse valor.
5. Execute os endpoints protegidos usando a autenticação Bearer configurada.

## Decisões técnicas

- O backend é a fonte da verdade para regras financeiras.
- Depósitos e saques ficam centralizados em `WalletService`.
- Controllers foram mantidos finos, delegando validação para Form Requests e regras para Services.
- Valores monetários são armazenados como centavos inteiros.
- Operações financeiras usam transações de banco e lock da wallet durante atualização de saldo.
- Cada operação bem-sucedida cria uma transação com `balance_after_cents`.
- Laravel Sanctum protege os endpoints autenticados.
- O frontend Nuxt consome a API Laravel diretamente via URL pública configurada.
- Docker Compose padroniza backend, frontend e PostgreSQL para execução local.

## Comandos úteis

```bash
docker compose up -d --build
docker compose exec backend php artisan optimize:clear
docker compose exec backend php artisan migrate:fresh --seed
docker compose exec backend php artisan test
docker compose exec frontend pnpm typecheck
docker compose exec frontend pnpm build
docker compose down
```
