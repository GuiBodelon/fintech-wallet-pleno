# Fintech Wallet Pleno

MVP de carteira digital pessoal para desafio fullstack pleno.

## Stack

- Backend: Laravel 13, PHP 8.3+, Sanctum, PostgreSQL
- Frontend: Nuxt 3 em SPA mode, Vue 3 Composition API, TypeScript, Pinia, TailwindCSS
- Infra local: Docker Compose

## Desenvolvimento local

```bash
docker compose up --build
```

Servicos locais:

- Frontend: http://localhost:3000
- Backend API: http://localhost:8000/api
- PostgreSQL: localhost:5432

## Comandos uteis

```bash
docker compose up -d
docker compose exec backend php artisan test
docker compose exec backend php artisan migrate:fresh --seed
docker compose exec frontend pnpm typecheck
```

## Usuarios seedados

Depois de executar `docker compose exec backend php artisan migrate:fresh --seed`, use:

- `demo@fintech.test` / `password`: usuario principal com wallet, saldo positivo e historico com creditos e debitos.
- `empty@fintech.test` / `password`: usuario com wallet zerada e sem transacoes.
- `lowbalance@fintech.test` / `password`: usuario com saldo baixo para testar saque insuficiente.
