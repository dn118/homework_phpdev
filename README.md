# Laravel + Livewire Article Favorites

## Requirements

- Docker Desktop (or Docker Engine + Docker Compose plugin)
- Python 3 (only for optional E2E script)

## First Run

```bash
docker compose up -d --build
docker compose exec app sh /var/www/html/docker/app/setup.sh
```

## Open Project

- App URL: http://localhost:8782
- Mailpit URL: http://localhost:8782/mailpit

## Useful Commands

Run tests:

```bash
docker compose exec app php artisan test
```

Run E2E auth flow:

```bash
python3 scripts/e2e_test.py
```

View logs:

```bash
docker compose logs -f app
docker compose logs -f nginx
docker compose logs -f queue
```

Stop all services:

```bash
docker compose down
```

Reset all containers and database volume:

```bash
docker compose down -v --remove-orphans
docker compose up -d --build
docker compose exec app sh /var/www/html/docker/app/setup.sh
```

## Notes

- No host PHP is required.
- `setup.sh` is idempotent and is the only bootstrap script.
- `entrypoint.sh` is minimal runtime only (no migrations, no composer install, no APP_KEY generation).
- Articles are synced from Hacker News during setup.
- Seeded user:
  - email: `admin@admin.com`
  - password: `qweqweqwe`
