<h1 align="center">Laravel API Boilerplate</h1>

<p align="center">
    <strong>A strictly-typed, production-ready Laravel 13 API starter.</strong><br>
    Domain-driven structure · Actions + Repositories · Auth, RBAC, Stripe, OAuth & OTP — batteries included.
</p>

<p align="center">
    <img src="https://img.shields.io/badge/PHP-8.4-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP 8.4">
    <img src="https://img.shields.io/badge/Laravel-13-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel 13">
    <img src="https://img.shields.io/badge/Tests-110%20passing-22C55E?style=flat-square&logo=pest&logoColor=white" alt="110 tests passing">
    <img src="https://img.shields.io/badge/PHPStan-level%20max-2A2A72?style=flat-square" alt="PHPStan level max">
    <img src="https://img.shields.io/badge/Style-Pint-FF2D20?style=flat-square" alt="Laravel Pint">
    <img src="https://img.shields.io/badge/License-MIT-blue?style=flat-square" alt="MIT License">
</p>

---

A clean foundation for building serious JSON APIs with Laravel. It is **API-only** (no Blade, no
sessions), organized into **self-contained domains**, and wired so that controllers stay thin:
validation lives in form requests, business logic in single-purpose **actions**, and every database
touch goes through a **repository interface**. Static analysis runs at **Larastan level max with zero
errors**, and the whole thing is covered by **110 passing tests**.

## ✨ Highlights

**Authentication & Access**
- **Sanctum** token authentication (register, login, logout, device tokens)
- **Spatie RBAC** — roles & granular permissions, `admin`/`user` out of the box
- **OAuth 2.0** social login (Google & Apple) via Socialite
- **Email verification** with signed URLs + **email OTP** flow for verifying/changing an email
- **Password reset** with a frontend-friendly reset URL

**Product features**
- **Admin API** — user CRUD with search, role filter, sort & pagination; role & permission management
- **Stripe subscriptions** via Cashier — plans, Checkout, billing portal, cancel/resume, webhooks
- **Audit trail** — every authenticated request is logged via Activitylog
- **Health check** (database + cache) for uptime monitoring
- **Auto-generated OpenAPI docs** via Scramble at `/docs/api`

**Engineering**
- **PHP 8.4** with `declare(strict_types=1)` everywhere, `final` classes, constructor promotion
- **Actions + Repositories + DTOs** with a uniform `ApiResponse` JSON envelope
- **PHPStan / Larastan** at level **max**, **Pint** for style, **Rector** for automated refactors
- **Pest** test suite · **Octane**-ready · **Docker Compose** (Nginx + PHP + Postgres)
- **GitHub Actions** CI (lint + test matrix)

## 🏗 Architecture

The app is organized by **domain folders under `app/`** instead of Laravel's default
`Http/Models/...` layout. Each domain owns its controllers, requests, DTOs, actions, resources,
models, and repositories. A request flows in one direction:

```
HTTP request
  → routes/api/*.php             versioned under /api/v1, grouped by access level
  → {Domain}\Requests\*Request   validation + authorization
  → {Domain}\Controllers\*       thin — no business logic
  → {Domain}\Data\*Data (DTO)    immutable, built from validated input
  → {Domain}\Actions\*Action     one public execute(); the business logic
  → {Domain}\Repositories\*      interface + Eloquent impl; all DB access
  → {Domain}\Resources\*Resource response shaping
  → Support\Http\ApiResponse     uniform JSON envelope: { data, message, meta? }
```

Repository interfaces are bound to their Eloquent implementations in `RepositoryServiceProvider`, so
domains depend on contracts, not Eloquent. Untyped input (validated arrays, query criteria) is read
through the typed `Support\Data\Input` helper rather than raw casts — which is part of how the
codebase stays clean at PHPStan level max.

> Full guide: **[ARCHITECTURE.md](ARCHITECTURE.md)** · Quick map for tools & agents: **[docs/PROJECT_MAP.md](docs/PROJECT_MAP.md)**

## 🧰 Tech stack

| Area | Choice |
|------|--------|
| Language / Framework | PHP 8.4 · Laravel 13 |
| Auth | Sanctum · Socialite (Google, Apple) |
| Authorization | Spatie laravel-permission |
| Billing | Laravel Cashier (Stripe) |
| Audit | Spatie laravel-activitylog |
| API docs | Scramble (OpenAPI) |
| Runtime | Laravel Octane-ready |
| Testing | Pest · PHPUnit |
| Quality | Larastan (level max) · Pint · Rector |
| Database | SQLite (dev) · PostgreSQL (prod) |

## 🚀 Quick start

```bash
git clone <repo-url> && cd laravel_api_only_boilerplate
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed   # seeds roles & permissions
php artisan test             # 110 passing
```

Serve it: `php artisan serve` (or `php artisan octane:start`). API docs at `http://localhost:8000/docs/api`.

### With Docker

```bash
docker compose up -d
```

App available at **http://localhost:8080** (Nginx → PHP, with PostgreSQL).

## 📡 API reference

All endpoints are prefixed with **`/api/v1`**. Interactive docs are generated by Scramble at
`/docs/api`. Auth column: **—** public · **✓** Sanctum token · **admin** requires the `admin` role.

### Authentication

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `POST` | `/register` | Create a new account | — |
| `POST` | `/login` | Obtain a Sanctum token | — |
| `POST` | `/logout` | Revoke the current token | ✓ |
| `POST` | `/forgot-password` | Request a password reset link | — |
| `POST` | `/reset-password` | Complete a password reset | — |

### Email verification & OTP

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `GET`  | `/email/verify/{id}/{hash}` | Verify email via signed URL | — |
| `POST` | `/email/verification-notification` | Re-send the verification email | ✓ |
| `POST` | `/user/email/send-otp` | Send an OTP to set/change the email | ✓ |
| `POST` | `/user/email/verify-otp` | Verify the OTP and apply the email | ✓ |

### OAuth

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `GET` | `/auth/{provider}/redirect` | Get the provider redirect URL | — |
| `GET` | `/auth/{provider}/callback` | Handle the OAuth callback (stateless) | — |

`{provider}` is `google` or `apple`.

### User profile

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `GET` | `/user` | Retrieve the authenticated user | ✓ |
| `PUT` | `/user` | Update profile (name, email) | ✓ |

### Subscriptions

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `GET`  | `/subscriptions/plans` | List available plans | — |
| `GET`  | `/subscriptions/current` | Current subscription status | ✓ |
| `POST` | `/subscriptions/checkout` | Create a Stripe Checkout session | ✓ |
| `POST` | `/subscriptions/portal` | Get the Stripe billing portal URL | ✓ |
| `POST` | `/subscriptions/cancel` | Cancel the active subscription | ✓ |
| `POST` | `/subscriptions/resume` | Resume a subscription on grace period | ✓ |
| `POST` | `/stripe/webhook` | Stripe webhook handler | — |

### Admin (requires `admin` role)

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET`    | `/admin/health` | Database + cache connectivity check |
| `GET`    | `/admin/users` | Paginated list (search, role filter, sort) |
| `GET`    | `/admin/users/{id}` | Show a user |
| `POST`   | `/admin/users` | Create a user (optional role) |
| `PUT`    | `/admin/users/{id}` | Update a user |
| `DELETE` | `/admin/users/{id}` | Delete a user (self-deletion blocked) |
| `GET`    | `/admin/roles` | List roles with permissions |
| `POST`   | `/admin/roles` | Create a role (optional permissions) |
| `PUT`    | `/admin/roles/{id}` | Update name / sync permissions |
| `DELETE` | `/admin/roles/{id}` | Delete a role (`admin` protected) |
| `GET`    | `/admin/permissions` | List all permissions |

Run `php artisan route:list --except-vendor` for the full, authoritative list.

## 📁 Project structure

```
app/
├── Auth/           # Register, login, password reset, email verification
├── User/           # Profile, email OTP, the User model & policy
├── Admin/          # Admin CRUD for users, roles, permissions
├── OAuth/          # Social login (Google, Apple) via Socialite
├── Subscription/   # Stripe checkout, portal, cancel/resume, plans, webhook
├── Health/         # Health check endpoint
├── Http/           # Base controller + middleware (ForceJson, request logging)
├── Providers/      # App + repository-binding service providers
└── Support/        # ApiResponse envelope · Data\Input typed reader
```

Each domain holds its own `Actions/`, `Controllers/`, `Data/`, `Models/`, `Requests/`,
`Resources/`, and `Repositories/` as needed.

## 🧪 Testing & quality

```bash
composer test          # lint → refactor → types → unit  (the full gate)
composer test:unit     # Pest only
composer test:coverage # Pest with coverage (min 90%)
composer analyse       # PHPStan / Larastan (level max)
composer lint          # auto-format with Pint
composer refactor      # apply Rector
```

| Command | What it checks |
|---------|----------------|
| `test:lint` | Pint code style (dry-run) |
| `test:refactor` | Rector (dry-run) |
| `test:types` | PHPStan / Larastan at level **max** |
| `test:unit` | Pest suite — **110 passing** |

## 🔄 CI

`.github/workflows/ci.yml` runs on every push / PR to `main`:

- **lint** — Laravel Pint on PHP 8.4
- **test** — Pest suite (PHP version matrix)

## 📄 License

Released under the [MIT License](LICENSE).
