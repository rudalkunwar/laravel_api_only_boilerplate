# Project Map

A compact, token-efficient guide to this codebase. Read this first; it tells you
**where** things live and **how** a request flows so you can jump straight to the
relevant file instead of scanning the whole tree.

> Laravel 13 (PHP 8.4) **API-only** boilerplate. Sanctum tokens, Spatie roles &
> permissions, Cashier (Stripe) subscriptions, Socialite OAuth, email OTP, and
> activity logging. Static analysis runs at **Larastan level max (0 errors)**;
> keep it that way.

## Architecture in one breath

The app is organised by **domain folders under `app/`** (not by Laravel's
default `Http/Models/...`). Each domain owns its own controllers, requests,
DTOs, actions, resources, models, and repositories.

```
HTTP request
  → routes/api/*.php            (versioned under /api/v1, grouped by access level)
  → {Domain}\Requests\*Request  (validation + authorize)
  → {Domain}\Controllers\*      (thin; no business logic)
  → {Domain}\Data\*Data DTO     (built via ::fromArray(), typed via Support\Data\Input)
  → {Domain}\Actions\*Action    (one public execute(); business logic)
  → {Domain}\Repositories\*     (interface + Eloquent impl; all DB access)
  → {Domain}\Resources\*Resource (response shaping)
  → Support\Http\ApiResponse    (uniform JSON envelope: {data, message, meta?})
```

Repository interfaces are bound to Eloquent implementations in
`app/Providers/RepositoryServiceProvider.php`. Cross-cutting config (model
strictness, rate limiters `api`/`auth`, password rules, `ResetPassword` URL,
policy registration) lives in `app/Providers/AppServiceProvider.php`.

## Domains (`app/`)

| Folder | Responsibility | Key files |
|---|---|---|
| `Auth/` | Register, login/logout, password reset, email verification | `Controllers/`, `Actions/` (Authenticate/Register/Logout/ResetUserPassword/SendPasswordResetLink), `Data/` (Login/Register/ResetPassword/AuthToken), `Enums/Role.php`, `Enums/Permission.php` |
| `User/` | Profile, email OTP, the `User` model + policy | `Models/User.php`, `Models/EmailOtp.php`, `Controllers/`, `Actions/` (UpdateUserProfile/SendEmailOtp/VerifyEmailOtp), `Repositories/UserRepository*`, `Policies/UserPolicy.php`, `Resources/UserResource.php` |
| `Admin/` | Admin CRUD for users, roles, permissions (`role:admin` gated) | `Controllers/` (User/Role/Permission), `Repositories/` (Role/Permission), `Requests/`, `Resources/UserResource.php` |
| `OAuth/` | Social login (Google/Apple) | `Controllers/SocialiteController.php`, `Actions/AuthenticateSocialUserAction.php`, `Models/SocialAccount.php`, `Repositories/SocialAccountRepository*`, `Data/` |
| `Subscription/` | Stripe checkout, portal, cancel/resume, plans, webhook | `Controllers/` (Subscription/Plan/Webhook), `Actions/` (CreateCheckout/Cancel/Resume), `Enums/Plan.php`, `Data/CheckoutData.php`, `Resources/` |
| `Health/` | `GET /admin/health` DB + cache check | `HealthController.php` |
| `Support/` | Shared, framework-agnostic helpers | `Http/ApiResponse.php` (response envelope), `Data/Input.php` (typed reads from `array<string,mixed>`) |
| `Http/` | App-level middleware | `Middleware/ForceJsonResponse.php`, `Middleware/LogRequest.php` (activity log) |
| `Providers/` | Bootstrapping & DI bindings | `AppServiceProvider.php`, `RepositoryServiceProvider.php` |

## Routes (`routes/`)

`api.php` wraps everything in `prefix('v1')->name('api.v1.')` and pulls in three
files by access level:

- **`api/public.php`** — no auth. OAuth redirect/callback, register, login,
  forgot/reset password (`throttle:auth`), signed email verify, public plans
  list, Stripe webhook.
- **`api/user.php`** — `auth:sanctum`. logout, resend verification, profile
  get/update, email OTP send/verify, all subscription actions (`throttle:api`).
- **`api/admin.php`** — `auth:sanctum` + `role:admin` + `throttle:api`, prefix
  `admin/`. health, user CRUD, role CRUD, permissions list.

Full endpoint list: `php artisan route:list --except-vendor`.

## Conventions (match these when adding code)

- **Controllers stay thin** — validation in a `FormRequest`, logic in an
  `Action`, DB in a `Repository`. Build a `Data` DTO from `$request->validated()`
  via `::fromArray()`.
- **Reading untyped arrays** (validated payloads, criteria) goes through
  `App\Support\Data\Input` (`string`/`nullableString`/`integer`/`boolean`/
  `stringList`) — never raw `(string) $data['x']` casts (keeps Larastan max clean).
- **All API responses** go through `App\Support\Http\ApiResponse`.
- **Typed config** reads use `Config::string(...)`, not `(string) config(...)`.
- `declare(strict_types=1)`, `final` classes, constructor property promotion,
  explicit return types, array-shape PHPDoc everywhere.

## Data layer (`database/`)

Migrations cover: users (email **nullable** for OTP/social), cache, jobs,
personal access tokens, Spatie permission tables, activity log, Cashier
customer/subscription/subscription-item columns, email OTPs, social accounts.
Seeders: `DatabaseSeeder` → `RolePermissionSeeder` (seeds roles/permissions from
`Auth\Enums\Role` + `Auth\Enums\Permission`).

## Tests (`tests/`)

Pest. Feature tests mirror the domain folders (`tests/Feature/{Auth,User,Admin,
OAuth,Subscription,Health,Support}`); unit tests under `tests/Unit`
(`ArchTest`, enum tests, DTO `DataTest`, `Support/InputTest`). Run:
`php artisan test --compact` (filter with `--filter=`).

## Quality gates (run before finalizing)

```
vendor/bin/pint --dirty --format agent     # format
vendor/bin/phpstan analyse --no-progress   # Larastan level max — must be 0 errors
php artisan test --compact                 # full suite
```
