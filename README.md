# Fintech Wallet Pleno

MVP de carteira digital pessoal desenvolvido para um desafio técnico fullstack pleno.

O backend já implementa autenticação, criação automática de wallet no cadastro, depósitos, saques, histórico de transações, resumo de dashboard, seed de usuários para testes manuais e testes automatizados para os fluxos críticos.

## Stack

**Backend**

- PHP 8.3+
- Laravel 13
- Laravel Sanctum
- PostgreSQL
- Eloquent ORM
- Form Requests
- Service Layer
- Testes automatizados

**Frontend**

- Nuxt 3 em modo SPA
- Vue 3 Composition API
- TypeScript
- Pinia
- TailwindCSS

**Infra**

- Docker Compose

## Configuração do ambiente local

Os arquivos `.env` reais não são versionados. Crie os arquivos locais a partir dos exemplos.

Linux/macOS:

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

## Execução com Docker

Suba o ambiente local com:

```bash
docker compose up -d --build
```

URLs locais:

| Serviço | URL |
|---|---|
| Frontend | http://localhost:3000 |
| Backend API | http://localhost:8000/api |
| PostgreSQL | localhost:5432 |

## Setup Laravel

Após subir os containers, execute:

```bash
docker compose exec backend php artisan key:generate
docker compose exec backend php artisan optimize:clear
docker compose exec backend php artisan migrate:fresh --seed
docker compose exec backend php artisan test
```

## Variáveis de ambiente

Principais variáveis do backend:

```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=fintech_wallet
DB_USERNAME=fintech_wallet
DB_PASSWORD=secret
```

Ao executar com Docker, `DB_HOST` deve ser `postgres`, pois esse é o nome do serviço no `docker-compose.yml`.

Variável principal do frontend:

```env
NUXT_PUBLIC_API_BASE_URL=http://localhost:8000/api
```

## Usuários seedados

Depois de executar `docker compose exec backend php artisan migrate:fresh --seed`, os usuários abaixo ficam disponíveis:

| Usuário | E-mail | Senha | Cenário |
|---|---|---|---|
| Demo User | `demo@fintech.test` | `password` | Usuário principal com wallet, saldo positivo e histórico com créditos e débitos |
| Empty Wallet User | `empty@fintech.test` | `password` | Wallet zerada e sem transações |
| Low Balance User | `lowbalance@fintech.test` | `password` | Saldo baixo para testar saque insuficiente |

## Autenticação da API

Endpoints protegidos exigem token Bearer.

Fluxo básico para testes:

1. Faça login em `POST /api/login`.
2. Copie o token retornado em `data.token`.
3. Envie nas próximas requisições o header:

```http
Authorization: Bearer <token>
```

## Endpoints da API

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
| `GET` | `/api/wallet` | Sim | Retorna a wallet do usuário autenticado |
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

## Regras financeiras

- Valores monetários são armazenados internamente em centavos inteiros.
- Operações financeiras não usam ponto flutuante.
- Depósito deve ter valor positivo.
- Saque deve ter valor positivo.
- Saque exige saldo suficiente.
- Toda operação bem-sucedida cria uma transação.
- Toda transação armazena o saldo após a operação em `balance_after_cents`.
- Operações de wallet são atômicas.
- A lógica financeira fica isolada em `WalletService`.
- Controllers são mantidos finos.

## Collection do Insomnia

A collection está disponível em:

```txt
docs/fintech-wallet-pleno-insomnia-collection.json
```

Para usar:

1. Abra o Insomnia.
2. Importe o arquivo JSON da collection.
3. Execute o login com um usuário seedado.
4. Copie o token retornado.
5. Configure a variável `auth_token` com esse valor.

## Comandos úteis

```bash
docker compose up -d
docker compose down
docker compose exec backend php artisan migrate:fresh --seed
docker compose exec backend php artisan test
docker compose exec backend php artisan optimize:clear
docker compose exec frontend pnpm typecheck
```

## Testes

Para executar os testes do backend:

```bash
docker compose exec backend php artisan test
```

Os testes cobrem fluxos críticos do backend, incluindo autenticação, criação de wallet no cadastro, depósitos, saques, falhas de validação, saldo insuficiente, histórico de transações e dashboard.

## Decisões técnicas

- Laravel concentra as regras de negócio.
- Nuxt é usado apenas como aplicação frontend.
- `WalletService` isola a lógica financeira.
- Dinheiro é armazenado como centavos inteiros.
- Docker Compose padroniza o ambiente local.
- PostgreSQL é usado como banco de dados.
- Laravel Sanctum protege os endpoints autenticados.
