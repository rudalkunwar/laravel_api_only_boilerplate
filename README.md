<p align="center">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel">
</p>

<p align="center">
    <strong>Laravel API Only Boilerplate</strong><br>
    A production-grade, headless Laravel 13 API scaffold with Octane, Sanctum, PostgreSQL, and Docker.
</p>

<p align="center">
    <img src="https://img.shields.io/badge/PHP-8.4-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP 8.4">
    <img src="https://img.shields.io/badge/Laravel-13-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel 13">
    <img src="https://img.shields.io/badge/PostgreSQL-17-4169E1?style=flat-square&logo=postgresql&logoColor=white" alt="PostgreSQL 17">
    <img src="https://img.shields.io/badge/Redis-7-DC382D?style=flat-square&logo=redis&logoColor=white" alt="Redis 7">
    <img src="https://img.shields.io/badge/Tests-Pest-CC0000?style=flat-square&logo=pest&logoColor=white" alt="Tests">
    <img src="https://img.shields.io/badge/CI-GitHub%20Actions-2088FF?style=flat-square&logo=githubactions&logoColor=white" alt="CI">
</p>

---

## Features

- **Laravel 13** on **PHP 8.4** with strict type declarations
- **Laravel Octane** (Swoole) for high-performance request handling
- **Sanctum** token-based API authentication (register, login, logout, password reset, email verification)
- **PostgreSQL 17** + **Redis 7** for storage and caching
- **Pest** + **PHPUnit** for testing, **Laravel Pint** for code style
- **PHPStan** (Larastan) static analysis at level 6
- **Rector** for automated upgrades and refactoring
- **Docker Compose** with Nginx, Octane, Postgres, and Redis
- **GitHub Actions** CI (lint + test on every push/PR)

## Requirements

- PHP 8.4+
- Composer 2
- PostgreSQL 17 / Redis 7 (or Docker)

## Installation

```bash
git clone https://github.com/rudalkunwar/laravel_api_only_boilerplate.git
cd laravel_api_only_boilerplate
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

## Usage

Start the development server:

```bash
php artisan serve
```

Or with Octane (Swoole):

```bash
php artisan octane:start --server=swoole --host=0.0.0.0 --port=8000
```

### Docker

Build and run the full stack (Nginx + Octane + Postgres + Redis):

```bash
docker compose up -d
```

The application will be available at `http://localhost:8080`.

## API Reference

All endpoints are prefixed with `/api/v1`.

### Authentication

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `POST` | `/register` | Create a new account | — |
| `POST` | `/login` | Obtain a Sanctum token | — |
| `POST` | `/logout` | Revoke current token | ✓ |
| `POST` | `/forgot-password` | Request password reset link | — |
| `POST` | `/reset-password` | Complete password reset | — |

### Email Verification

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `GET` | `/email/verify/{id}/{hash}` | Verify email address | — |
| `POST` | `/email/verification-notification` | Re-send verification email | ✓ |

### User Profile

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `GET` | `/user` | Retrieve authenticated user | ✓ |
| `PUT` | `/user` | Update profile | ✓ |

## Scripts

| Command | Description |
|---------|-------------|
| `composer lint` | Auto-format code with Pint |
| `composer test:lint` | Check code style (dry-run) |
| `composer test:unit` | Run the Pest test suite |
| `composer test:coverage` | Run tests with coverage report |
| `composer test` | Run lint check + full test suite |
| `composer analyse` | Static analysis with PHPStan |
| `composer refactor` | Dry-run Rector refactoring |

## CI/CD

GitHub Actions runs on every push or pull request to `main`:

- **lint** — Validates code style with Laravel Pint on PHP 8.4
- **test** — Runs the full Pest test suite on PHP 8.4

## Project Structure

```
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── Api/V1/        # Versioned API controllers
│   └── Models/
├── config/                     # Application configuration
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── docker/                     # Docker configuration files
├── routes/
│   └── api.php                 # API route definitions
├── tests/
│   ├── Feature/                # Feature tests
│   └── Unit/                   # Unit tests
├── Dockerfile
├── docker-compose.yml
└── pint.json                   # Pint configuration
```

## License

[MIT](LICENSE)
