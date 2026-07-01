# AGENTS.md

## Project

This repository is a fullstack technical challenge for a fintech wallet MVP.

Backend:
- Laravel 13
- PHP 8.3+
- PostgreSQL
- Laravel Sanctum
- REST API
- Service layer for wallet operations

Frontend:
- Nuxt 3 SPA
- TypeScript
- TailwindCSS
- Pinia

## Main source of truth

Use `docs/codex-scope.md` as the product and technical scope.

Do not reinterpret the challenge beyond the documented requirements.
Do not add transfers, microservices, queues, CQRS, Event Sourcing or unnecessary DDD abstractions.

## Work rules

- Make small, incremental changes.
- Do not re-scaffold the project unless explicitly requested.
- Do not install new production dependencies without asking first.
- Prefer simple Laravel services over unnecessary repository layers.
- Keep controllers thin.
- Put wallet business rules inside services.
- Store monetary values as integer cents.
- Use database transactions for deposit and withdraw operations.
- Keep frontend as Nuxt SPA consuming the Laravel API.
- Do not use Nuxt server routes for business logic.

## Verification

When changing backend:
- Run the most relevant PHPUnit/Pest tests.
- Avoid running the full Docker stack unless needed.

When changing frontend:
- Run typecheck.
- Run lint only if already configured.

## Response format

After each task, return only:
- Summary
- Files changed
- Commands executed
- Tests result
- Suggested commit message
- Next recommended step