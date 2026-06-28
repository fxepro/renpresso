# Renpresso

Standalone Laravel application — property management platform (Renpresso rebrand).

**Replaces:** [`../RentersMaxx/`](../RentersMaxx/) once migration is complete. Do not add nested project folders.

## Stack

- Laravel 11, PHP 8.2+
- Blade (no Next.js)
- Vite + CSS token layer
- PostgreSQL / Redis (production)

## Frontend architecture

Follows [frontend-design-layout-standard.md](../Documentation/frontend-design-layout-standard.md) (Blade mapping):

```
resources/views/
├── layouts/
│   ├── marketing.blade.php   # MarketingShell — header, main, footer
│   ├── utility.blade.php     # Legal / narrow prose (same chrome)
│   └── app.blade.php         # Alias → marketing
├── partials/
│   ├── layout/               # site-header, site-footer, shell-scripts
│   └── sections/             # page-header, hero, cta, …
└── pages/                    # Thin composition (migrate from inline CSS)

config/
├── site.php          # Brand metadata
├── navigation.php    # Header + mobile nav
├── footer.php        # Footer columns + legal row
└── ctas.php          # CTA copy

resources/css/
├── tokens.css        # Single source for colors / type
├── components.css    # .page-main, .page-body, containers
├── utility.css       # Legal prose, PageHeader
└── app.css           # Imports above + legacy section styles
```

## Setup

```bash
cd c:\AIProjects\renpresso
copy .env.example .env
composer install   # if vendor missing
php artisan key:generate
npm install
npm run build
php artisan migrate --seed
php artisan serve
```

## Migration status

| Area | Status |
|------|--------|
| Standalone repo | Done |
| Config-driven nav/footer | Done |
| MarketingShell + Vite CSS | Done |
| All 19 public pages on shell | Done |
| Utility pages (privacy, terms, cookies) | Done |
| Auth shell (login, register) | Done |
| Section partials + token cleanup | Next — see [OVERALL-VERDICT.md](docs/OVERALL-VERDICT.md) |
| Dashboard / admin shells | Unchanged (working) |

See [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) and [docs/MIGRATION.md](docs/MIGRATION.md).

## Deploy (Railway)

Repo: [github.com/xmash/renpresso](https://github.com/xmash/renpresso)

### 1. Create project

1. Open [Railway](https://railway.com) → **New Project** → **Deploy from GitHub repo** → select `xmash/renpresso`.
2. Railway uses the root `Dockerfile` and `railway.json` (health check: `/up`).

### 2. Add PostgreSQL

1. In the project: **+ New** → **Database** → **PostgreSQL**.
2. On the **web service** → **Variables** → **Add variable reference** → link Postgres `DATABASE_URL` (or individual `PG*` vars).

### 3. Redis (optional)

Add **Redis** for cache/session/queue, or use file drivers (see `.env.example` commented block).

If using Redis, reference `REDIS_URL` from the Redis service.

### 4. Required variables (web service)

Set on the **Renpresso web service** (generate key locally: `php artisan key:generate --show`):

| Variable | Example |
|----------|---------|
| `APP_KEY` | `base64:...` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://your-service.up.railway.app` |
| `DB_CONNECTION` | `pgsql` |

If not using `DATABASE_URL` reference: set `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` from Postgres.

**Without Redis** (minimal):

```
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

**With Redis** (recommended):

```
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_CLIENT=phpredis
```

Plus Redis host/password from the Redis plugin.

### 5. Deploy

Push to `main` — Railway redeploys automatically. First deploy runs migrations via `docker/entrypoint.sh`.

### CLI (optional)

```bash
railway login
railway link
railway up
```

Same Railway/Docker setup as the legacy RentersMaxx stack — deploy root is this repo.
