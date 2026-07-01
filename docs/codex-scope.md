# Technical Challenge Scope — Laravel 13 + Nuxt 3 Fintech Wallet MVP

## 0. Purpose of this document

This document reformats the original technical challenge into an implementation-oriented scope for Codex.

The goal is to build a functional MVP for a personal digital wallet with deposits, withdrawals, authentication, transaction history, dashboard, tests, documentation, deploy, and clean project organization.

Codex must preserve the challenge constraints and avoid unnecessary complexity.

---

## 1. Original challenge summary

**Challenge:** Digital Wallet with Deposits and Withdrawals  
**Level:** Full Stack Pleno  
**Business context:** Fintech / personal wallet  
**Original base stack:** Laravel 10+ / Vue.js 3  
**Delivery:** Public GitHub repository + public deploy  
**Deadline:** 3 calendar days

A fintech startup is building a personal digital wallet. Authenticated users must manage their own balance by performing deposits and withdrawals with security controls and a complete history of financial movements.

The system must demonstrate:

- Good layered organization.
- Thin controllers.
- Business logic isolated in services.
- Correct financial rules.
- Edge case handling.
- Automated tests covering critical flows.
- Functional deploy.
- Clear documentation.

---

## 2. Final selected stack for this implementation

Use the stack below. It intentionally aligns the MVP with the job description while keeping the challenge simple.

Important: the official challenge requires **Laravel 10+ / Vue.js 3**. This implementation will use **Laravel 13** and **Nuxt 3 in SPA mode with TypeScript**, which satisfies the original stack requirement while matching the preferred stack for this project.

### 2.1 Backend

- **PHP version compatible with Laravel 13**
- **Laravel 13**
- **Laravel Sanctum** for token authentication
- **PostgreSQL** as database
- **Eloquent ORM**
- **Form Requests** for input validation
- **Service Layer** for wallet operations
- **Database transactions** for atomic financial operations
- **Pest or PHPUnit** for automated tests

### 2.2 Frontend

- **Nuxt 3**
- **Vue 3 Composition API**
- **TypeScript**
- **Nuxt in SPA mode**: `ssr: false`
- **TailwindCSS**
- **Pinia** for state management
- **Vue Router via Nuxt pages**
- **Axios or `$fetch`** for API communication

### 2.3 Infrastructure

- **GitHub public repository**
- **Docker Compose** for local organization of backend, frontend, PostgreSQL, and supporting services
- Docker is welcome in the official challenge but does not significantly affect evaluation by itself; for this implementation it is a project decision, not a requirement expansion
- **Public deploy** using Railway, Render, Fly.io, or equivalent
- **Organized commits**
- **README.md with setup, decisions, deploy link, and test credentials**

---

## 3. Important architectural decision

Use **Nuxt 3 only as the frontend application**.

Do **not** use Nuxt server routes, Nitro APIs, backend-for-frontend logic, SSR, or duplicate business rules in the frontend.

Recommended Nuxt configuration:

```ts
export default defineNuxtConfig({
  ssr: false,

  modules: [
    '@pinia/nuxt',
    '@nuxtjs/tailwindcss',
  ],

  typescript: {
    strict: true,
  },

  runtimeConfig: {
    public: {
      apiBaseUrl: process.env.NUXT_PUBLIC_API_BASE_URL,
    },
  },
})
```

Reasoning:

- The job description mentions Nuxt 3, TailwindCSS, Pinia/Vuex, TypeScript, Laravel, Docker, CI/CD, and scalable architecture.
- The original challenge asks for Vue 3, Composition API, Pinia, routing, API communication, and visual feedback.
- Nuxt 3 in SPA mode satisfies both without adding unnecessary backend complexity.

---

## 4. Core business scope

## 4.1 Authentication

Implement:

- User registration with:
  - name
  - email
  - password
- Login
- Logout
- Protected routes using Laravel Sanctum token authentication
- When a user registers, automatically create a wallet with initial balance of **R$ 0.00**

Expected behavior:

- Only authenticated users can access wallet, dashboard, and transaction history.
- Users can only access their own wallet and own transactions.

---

## 4.2 Wallet operations

Implement two wallet operations:

### Deposit

The authenticated user adds money to their own wallet.

Rules:

- Amount must be positive.
- Amount must be greater than zero.
- Operation must create a transaction history record.
- Transaction type must be `credit`.
- Operation must be atomic.
- If anything fails, no data must be persisted.

### Withdrawal

The authenticated user removes money from their own wallet.

Rules:

- Amount must be positive.
- Amount must be greater than zero.
- User must have enough balance.
- It is not allowed to withdraw a fractional amount below **R$ 0.01**.
- Operation must create a transaction history record.
- Transaction type must be `debit`.
- Operation must be atomic.
- If anything fails, no data must be persisted.

### Financial precision rule

Store all money values as integer cents.

Examples:

| Display value | Stored value |
|---:|---:|
| R$ 0.01 | 1 |
| R$ 10.00 | 1000 |
| R$ 125.49 | 12549 |

Do not use floating point values for money calculations.

---

## 4.3 Transaction history

Implement a paginated listing of the authenticated user's transactions.

Each transaction must display:

- Date/time
- Type: `credit` or `debit`
- Amount
- Balance after the operation

Filters:

- By type:
  - credit
  - debit
- By period:
  - initial date
  - final date

Rules:

- User can only see their own transactions.
- Results must be paginated.
- API must return pagination metadata.

---

## 4.4 Dashboard

The dashboard must show:

- Current wallet balance
- Last 5 transactions
- Total deposited in the current month
- Total withdrawn in the current month

---

## 4.5 Frontend screens

Implement the following screens:

### Login page

- Email input
- Password input
- Login button
- Error feedback

### Register page

- Name input
- Email input
- Password input
- Password confirmation input
- Register button
- Error feedback

### Dashboard page

- Current balance card
- Current month summary:
  - total deposited
  - total withdrawn
- Last 5 transactions
- Quick navigation to deposit, withdraw, and full history

### Deposit form

- Amount input
- Submit button
- Success feedback
- Validation error feedback

### Withdrawal form

- Amount input
- Submit button
- Frontend validation for insufficient balance
- Backend validation still required
- Success feedback
- Validation error feedback

### Full transaction history page

- Paginated transaction table
- Filter by type
- Filter by initial date
- Filter by final date
- Clear filters action

---

## 5. Backend requirements

Use Laravel 13.

Official requirement note: the challenge asks for Laravel 10 or superior, so Laravel 13 is acceptable as long as the implementation stays simple, functional, and well documented.

Required:

- RESTful API with standardized JSON responses
- Migrations
- Seeders
- Laravel Sanctum authentication
- Form Requests for input validation
- Service Layer for deposit and withdrawal logic
- Thin controllers
- Clear error handling
- Correct HTTP status codes
- At least 5 automated tests covering critical scenarios
- PostgreSQL or MySQL; use PostgreSQL for this implementation

Do not put business rules directly inside controllers.

---

## 6. Suggested backend structure

```txt
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── TransactionController.php
│   │   │   └── WalletController.php
│   │   ├── Requests/
│   │   │   ├── LoginRequest.php
│   │   │   ├── RegisterRequest.php
│   │   │   ├── WalletOperationRequest.php
│   │   │   └── TransactionFilterRequest.php
│   │   └── Resources/
│   │       ├── TransactionResource.php
│   │       └── WalletResource.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Wallet.php
│   │   └── Transaction.php
│   ├── Services/
│   │   └── WalletService.php
│   └── Enums/
│       └── TransactionType.php
├── database/
│   ├── migrations/
│   └── seeders/
└── tests/
    └── Feature/
        ├── AuthTest.php
        ├── WalletDepositTest.php
        ├── WalletWithdrawalTest.php
        ├── TransactionHistoryTest.php
        └── DashboardTest.php
```

---

## 7. Suggested frontend structure

```txt
frontend/
├── assets/
│   └── css/
│       └── main.css
├── components/
│   ├── wallet/
│   │   ├── BalanceCard.vue
│   │   ├── MonthlySummary.vue
│   │   ├── DepositForm.vue
│   │   ├── WithdrawForm.vue
│   │   └── TransactionTable.vue
│   └── ui/
│       ├── AppButton.vue
│       ├── AppInput.vue
│       ├── AppAlert.vue
│       └── AppPagination.vue
├── composables/
│   ├── useApi.ts
│   ├── useAuth.ts
│   └── useWallet.ts
├── middleware/
│   └── auth.ts
├── pages/
│   ├── index.vue
│   ├── login.vue
│   ├── register.vue
│   ├── deposit.vue
│   ├── withdraw.vue
│   └── transactions.vue
├── stores/
│   ├── auth.store.ts
│   └── wallet.store.ts
├── types/
│   ├── auth.ts
│   ├── wallet.ts
│   └── transaction.ts
└── nuxt.config.ts
```

---

## 8. Suggested database model

## 8.1 users

Use Laravel default users table, with necessary fields:

- id
- name
- email
- password
- timestamps

## 8.2 wallets

Fields:

- id
- user_id
- balance_cents
- timestamps

Rules:

- `user_id` must be unique if each user has only one wallet.
- `balance_cents` must be an integer and default to 0.

## 8.3 transactions

Fields:

- id
- wallet_id
- user_id
- type
- amount_cents
- balance_after_cents
- timestamps

Rules:

- `type` must support `credit` and `debit`.
- `amount_cents` must be positive.
- `balance_after_cents` must represent the wallet balance immediately after the operation.

---

## 9. Suggested API endpoints

Base path: `/api`

### Auth

```txt
POST   /api/register
POST   /api/login
POST   /api/logout
GET    /api/me
```

### Wallet

```txt
GET    /api/wallet
POST   /api/wallet/deposit
POST   /api/wallet/withdraw
```

### Transactions

```txt
GET    /api/transactions
```

Query parameters:

```txt
type=credit|debit
from=YYYY-MM-DD
to=YYYY-MM-DD
page=1
per_page=10
```

### Dashboard

```txt
GET    /api/dashboard
```

---

## 10. API response standard

Use consistent JSON response shapes.

### Success example

```json
{
  "success": true,
  "message": "Deposit completed successfully.",
  "data": {}
}
```

### Validation error example

```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": {
    "amount": ["The amount must be greater than zero."]
  }
}
```

### Business error example

```json
{
  "success": false,
  "message": "Insufficient balance."
}
```

Use proper HTTP status codes:

| Scenario | Status |
|---|---:|
| Success | 200 |
| Created | 201 |
| Validation error | 422 |
| Unauthorized | 401 |
| Forbidden access | 403 |
| Not found | 404 |
| Business rule conflict | 409 |
| Server error | 500 |

---

## 11. WalletService requirements

Create a `WalletService` responsible for the financial operations.

Required methods:

```php
public function deposit(User $user, int $amountCents): Transaction;

public function withdraw(User $user, int $amountCents): Transaction;
```

Rules:

- Use `DB::transaction()`.
- Lock the wallet row during balance update.
- Validate positive amount.
- Validate sufficient balance on withdrawal.
- Update wallet balance.
- Create transaction history.
- Store balance after operation.
- Throw domain/business exceptions for invalid operations.

Do not let controllers calculate balances.

---

## 12. Automated tests

Implement at least 5 relevant tests.

Recommended tests:

1. User registration creates wallet with zero balance.
2. Authenticated user can deposit a valid amount.
3. Deposit rejects zero or negative amount.
4. Authenticated user can withdraw when balance is sufficient.
5. Withdrawal rejects insufficient balance.
6. Withdrawal rejects amount below R$ 0.01.
7. Transaction history returns only authenticated user's transactions.
8. Dashboard returns current balance, last 5 transactions, monthly deposited total, and monthly withdrawn total.

Priority:

- Critical business rules first.
- Edge cases over cosmetic tests.
- Backend tests are more important than frontend tests for this challenge.

---

## 13. Frontend behavior requirements

Frontend must:

- Use Composition API.
- Use Pinia for auth and wallet state.
- Use TypeScript types for API payloads.
- Persist auth token safely enough for the MVP.
- Redirect unauthenticated users to login.
- Show loading states.
- Show success feedback after deposit and withdrawal.
- Show validation errors from API.
- Validate insufficient balance on withdrawal before sending request.
- Refresh dashboard data after wallet operations.
- Keep business rules authoritative in the backend.

---

## 14. What not to implement

Avoid over-engineering.

Do not implement:

- Transfers between users.
- Microsservices.
- Queues.
- Event Sourcing.
- CQRS.
- Full DDD tactical modeling.
- Complex repository pattern unless needed.
- 100% test coverage.
- Advanced UI animations.
- Complex admin panel.
- Multi-wallet support.
- Currency conversion.
- Payment gateways.
- Real banking integration.

A simple, clean, functional, tested implementation is better than an elaborate architecture that goes beyond scope.

---

## 15. Evaluation criteria

The original challenge prioritizes the following:

| Criterion | What is evaluated | Weight |
|---|---|---|
| Architecture & Organization | Thin controller, Service with isolated business logic | High |
| Business Rules | Deposit, withdrawal, validations | High |
| Code Quality | Readability, naming, low duplication | High |
| Deploy | Public functional application | High |
| Tests | 5 relevant tests, including failure scenarios | Medium |
| API Design | RESTful consistency, correct HTTP status, standardized errors | Medium |
| Frontend | Functional flow, visual feedback, Composition API | Medium |
| Documentation | Technical decisions, how to run, deploy link, credentials | Medium |
| Git History | Descriptive commits and logical progression | Low |

---

## 16. Delivery instructions

Required delivery:

1. Create a public GitHub repository.
2. Suggested repository name: `fintech-wallet-pleno`.
3. Deploy the full application to a public platform.
4. Include repository link in README.
5. Include deploy link in README.
6. Include local setup instructions in README.
7. Include test credentials for a seeded user.
8. Send the repository link and deploy link through the agreed channel.

---

## 17. README checklist

The final README must include:

- [ ] Brief project description.
- [ ] Relevant technical decisions.
- [ ] Prerequisites:
  - PHP version
  - Composer version
  - Node version
  - Database version
- [ ] How to install backend dependencies.
- [ ] How to install frontend dependencies.
- [ ] How to configure backend `.env`.
- [ ] How to configure frontend `.env`.
- [ ] How to run migrations.
- [ ] How to run seeders.
- [ ] How to start backend server.
- [ ] How to start frontend server.
- [ ] How to run automated tests.
- [ ] Public deploy link.
- [ ] Seed user credentials:
  - email
  - password

---

## 18. Recommended implementation order for Codex

Follow this order to avoid rework:

1. Initialize repository structure.
2. Create Laravel 13 backend.
3. Configure PostgreSQL.
4. Install and configure Sanctum.
5. Create migrations for users, wallets, and transactions.
6. Create models and relationships.
7. Implement auth endpoints.
8. Implement wallet creation on registration.
9. Implement WalletService.
10. Implement deposit endpoint.
11. Implement withdrawal endpoint.
12. Implement transaction history endpoint with filters and pagination.
13. Implement dashboard endpoint.
14. Add seed user.
15. Add backend tests.
16. Create Nuxt 3 frontend in SPA mode.
17. Configure TailwindCSS and Pinia.
18. Implement API client/composable.
19. Implement auth store.
20. Implement login and register pages.
21. Implement dashboard page.
22. Implement deposit page/form.
23. Implement withdrawal page/form.
24. Implement transactions page/table with filters and pagination.
25. Add loading/error/success states.
26. Update README.
27. Prepare deploy.
28. Validate deploy.
29. Final pass: run tests, check lint, verify README, verify public links.

---

## 19. Git workflow recommendation

Use clear, incremental commits.

### 19.1 Repository configuration

The local workspace should be initialized as a Git repository and connected to the public GitHub repository created for the challenge:

```txt
Repository: GuiBodelon/fintech-wallet-pleno
Remote URL: https://github.com/GuiBodelon/fintech-wallet-pleno.git
Default branch: main
```

Recommended initial setup from this directory:

```bash
git init
git branch -M main
git remote add origin https://github.com/GuiBodelon/fintech-wallet-pleno.git
git status
```

If the remote repository already contains files, fetch and reconcile them before the first push. If it is empty, create the first commit locally and push with:

```bash
git add .
git commit -m "chore: initialize challenge roadmap"
git push -u origin main
```

Suggested progression:

```txt
chore: initialize project structure
chore: setup laravel backend
chore: setup nuxt frontend
feat(auth): implement registration and login
feat(wallet): create wallet on user registration
feat(wallet): implement deposit operation
feat(wallet): implement withdrawal operation
feat(transactions): add history filters and pagination
feat(dashboard): add wallet summary endpoint
feat(frontend): implement authentication screens
feat(frontend): implement wallet dashboard
feat(frontend): implement deposit and withdrawal flows
feat(frontend): implement transaction history page
test(wallet): cover critical wallet operations
docs: add setup and deploy instructions
```

---

## 20. Final quality checklist

Before delivery, verify:

- [ ] User can register.
- [ ] Registered user receives wallet with R$ 0.00.
- [ ] User can login.
- [ ] User can logout.
- [ ] Authenticated routes are protected.
- [ ] User can deposit valid amount.
- [ ] Invalid deposit is rejected.
- [ ] User can withdraw with sufficient balance.
- [ ] Withdrawal with insufficient balance is rejected.
- [ ] Amount below R$ 0.01 is rejected.
- [ ] Transaction is created for each successful operation.
- [ ] Balance after operation is stored correctly.
- [ ] Transaction history is paginated.
- [ ] Transaction filters work.
- [ ] Dashboard returns correct values.
- [ ] Frontend shows success feedback.
- [ ] Frontend shows validation errors.
- [ ] Backend tests pass.
- [ ] README is complete.
- [ ] Public deploy works.
- [ ] Git history is organized.

---

## 21. Codex execution instruction

Implement this project with a practical MVP mindset.

Prioritize:

- Correct business rules.
- Atomic wallet operations.
- Service Layer.
- Thin controllers.
- Clear validation.
- Useful tests.
- Functional frontend.
- Public deploy readiness.
- Clear README.

Avoid unnecessary abstractions.

Do not add features outside the defined scope.
