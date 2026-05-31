# Laravel API Only Boilerplate

A production-ready Laravel API boilerplate with Laravel Octane (Swoole), Sanctum auth, Spatie permissions, Docker, and full CI.

## Stack

- **Laravel** 11/12/13
- **Laravel Octane** with Swoole
- **Sanctum** token-based API auth
- **Spatie Laravel Permission** roles & permissions
- **Pest** testing
- **Laravel Pint** code style
- **Docker** with Nginx + Postgres + Redis

## Quick Start

```bash
# install dependencies
composer install

# set up environment
cp .env.example .env
php artisan key:generate

# run tests
composer test
```

## Docker

```bash
# build and start all services
docker compose up -d

# app at http://localhost:8080
```

## Scripts

| Command | Description |
|---|---|
| `composer lint` | Format code with Pint |
| `composer test` | Run lint + tests |
| `composer test:unit` | Run Pest tests |
| `composer test:coverage` | Run tests with coverage |
| `composer test:lint` | Check code style |

## CI

GitHub Actions runs on push/PR to `main`:
- **lint** — Pint on PHP 8.4
- **test** — Pest on PHP 8.3 & 8.4
