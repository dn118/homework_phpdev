# Laravel + Livewire Article Favorites

Take-home project implementing:

- registration, login, logout
- email verification
- article listing from a public API (Hacker News)
- per-user hide/unhide
- per-user favorite/unfavorite
- favorites pinned to top with newest favorite first

## Stack

- PHP 8.4, Laravel 13, Livewire
- Docker runtime with MySQL 8.4 + Mailpit
- Pest/PHPUnit for tests
- Laravel Pint + Larastan/PHPStan

## Prerequisites

- Docker + Docker Compose
- `make`

## Quick Start

```bash
make dev
```

`make dev` will:

1. create `.env.docker` from `.env.docker.example` (if missing)
2. generate `APP_KEY` in `.env.docker` (if missing)
3. build and start containers
4. run database migrations + seeders
5. sync articles from Hacker News

## URLs

- App: http://localhost:8780
- Mailpit UI: http://localhost:8025

## Seeded User

- email: `admin@admin.com`
- password: `qweqweqwe`
- email status: verified

## Day-to-Day Commands

```bash
make dev
make reset
make up
make down
make restart
make ps
make logs
make logs_db
make shell
make sync_articles
make test
```

## Fast Verification/Reset Links

Generate an email confirmation link (without opening Mailpit):

```bash
make email_confirmation_link EMAIL=user@example.com
```

Generate a password reset link:

```bash
make password_reset_link EMAIL=user@example.com
```

## Mailpit Workflow

1. Register a new user in the app.
2. Open Mailpit at `http://localhost:8025`.
3. Open the email.
4. Click the confirmation link.

## Quality Commands

```bash
make cs_fix
make stan
make audit
make code_check
```

## Notes

- Runtime DB is MySQL in Docker.
- Tests run in Docker via `make test`.
- External API calls are isolated to article sync; UI reads from local DB.
