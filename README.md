# EarnWise App

Commission-note management system built with Laravel 13, Inertia.js, and Vue 3.

Reviewers do not need PHP, Composer, or Node installed locally. Everything runs inside Docker.

---

## Requirements

| Tool | Minimum version |
|------|----------------|
| [Docker Desktop](https://www.docker.com/products/docker-desktop/) | 24 |
| Docker Compose | v2 (bundled with Docker Desktop) |

---

## Services and ports

| Service | Image | Host port | Purpose |
|---------|-------|-----------|---------|
| `app` | Custom PHP-FPM 8.3 | — | Laravel application |
| `web` | nginx:alpine | **8080** | HTTP entry point |
| `db` | mariadb:11 | **3306** | Persistent database |

The app is available at **<http://localhost:8080>** once the stack is running.

---

## Environment variables

Docker Compose injects the variables below into the `app` container at runtime.
They are set in `docker-compose.yml` and do not need to be changed for local development.

| Variable | Value | Notes |
|----------|-------|-------|
| `APP_KEY` | *(generated — see setup)* | Must be set before first boot |
| `APP_ENV` | `local` | |
| `DB_CONNECTION` | `mysql` | |
| `DB_HOST` | `db` | Internal service name |
| `DB_PORT` | `3306` | |
| `DB_DATABASE` | `earn_wise_db` | |
| `DB_USERNAME` | `earn_wise_user` | |
| `DB_PASSWORD` | `secret` | |
| `MYSQL_ROOT_PASSWORD` | `rootsecret` | MariaDB container only |

All other app settings come from the `.env` file (copied from `.env.example` during setup).

---

## First-time setup

Run these commands once after cloning the repository.

```bash
# 1. Copy the environment file
cp .env.example .env

# 2. Build the app image and generate an application key
#    This writes APP_KEY=base64:... into the .env file via the volume mount.
docker compose build app
docker compose run --rm --no-deps app php artisan key:generate

# 3. Start all services
docker compose up -d

# 4. Run database migrations
docker compose exec app php artisan migrate

# 5. Seed the database (roles, permissions, demo users, sample data)
docker compose exec app php artisan db:seed
```

The app is now running at **<http://localhost:8080>**.

---

## Seed data

The seeder creates the following out-of-the-box accounts and data.

### Roles and permissions

| Role | Permissions |
|------|-------------|
| `manager` | view commission notes, manage commission notes |
| `viewer` | view commission notes |

### Demo users

| Email | Password | Role |
|-------|----------|------|
| `manager@example.com` | `password` | manager |
| `viewer@example.com` | `password` | viewer |

### Sample data

- **Company:** Spar
    - **Branch:** Spar Bellville — one commission note (R 10 000)
    - **Branch:** Spar Gardens — one commission note (R 20 000)

---

## Running tests

Tests use an in-memory SQLite database (configured in `phpunit.xml`) and do not require the `db` container to be running.

```bash
# Run the full test suite
docker compose exec app ./vendor/bin/pest

# Run a specific test file
docker compose exec app ./vendor/bin/pest tests/Feature/CommissionNoteTest.php

# Run tests matching a description
docker compose exec app ./vendor/bin/pest --filter "creates a note"
```

If the `app` container is not running, start it first:

```bash
docker compose up -d app
docker compose exec app ./vendor/bin/pest
```

---

## Day-to-day commands

```bash
# Start the stack
docker compose up -d

# Stop the stack (preserves database volume)
docker compose down

# Stop and wipe the database volume
docker compose down -v

# Rebuild the app image after Dockerfile or dependency changes
docker compose up -d --build

# Open a shell inside the app container
docker compose exec app bash

# Run an Artisan command
docker compose exec app php artisan <command>

# Run a fresh migration and re-seed
docker compose exec app php artisan migrate:fresh --seed

# Tail Laravel logs
docker compose exec app tail -f storage/logs/laravel.log
```

---

## Tech stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 13, PHP 8.3 |
| Frontend | Vue 3, Inertia.js, Tailwind CSS |
| Database | MariaDB 11 (Docker) / SQLite in-memory (tests) |
| Auth & RBAC | Spatie Laravel Permission |
| Testing | Pest 4 |
| Asset bundling | Vite |
| Code style | Laravel Pint (PHP), ESLint + Prettier (JS) |

---

## Docker build optimization

- The Dockerfile now uses multi-stage builds for Node and Composer dependencies, reducing image size and improving build times.
- To maximize build cache usage, avoid unnecessary changes to `composer.json`, `composer.lock`, `package.json`, and `package-lock.json` unless dependencies change.
- The `.dockerignore` file excludes files and folders not needed for the build context, further speeding up builds.

### Recommended build command

```bash
# Build the app image using cache
# (Rebuild only if dependencies or Dockerfile change)
docker compose build app
```
