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

Repo (Railway): [github.com/fxepro/renpresso](https://github.com/fxepro/renpresso)  
Mirror: [github.com/xmash/renpresso](https://github.com/xmash/renpresso)

### 1. Create project

1. Open [Railway](https://railway.com) → **New Project** → **Deploy from GitHub repo** → select **`fxepro/renpresso`**.
2. Railway uses the root `Dockerfile` and `railway.json` (health check: `/up`).

### 2. PostgreSQL (web service variables)

Open your **app/web service** (not the Postgres service) → **Variables**.

Set **`DB_CONNECTION`** manually (plain text, not a reference):

```
DB_CONNECTION=pgsql
```

Then use **one** of the two options below — not both mixed incorrectly.

#### Option A — one URL (simplest)

| Variable on web service | How to set |
|---------------------------|------------|
| `DATABASE_URL` | **Add variable reference** → select **Postgres** service → **`DATABASE_PRIVATE_URL`** (preferred, same Railway project) or **`DATABASE_URL`** |

Do **not** set `DB_HOST`, `DB_PORT`, etc. when using a URL.

#### Option B — individual fields (Railway `PG*` → Laravel `DB_*`)

Railway Postgres exposes **`PGHOST`**, **`PGPORT`**, **`PGUSER`**, **`PGPASSWORD`**, **`PGDATABASE`** on the database service. The app does **not** see them until you **reference** each one on the web service.

On the **web service**, add **variable references** (name left column = what Laravel reads, value = reference to Postgres):

| Variable name (web service) | Reference from Postgres service |
|-----------------------------|----------------------------------|
| `DB_HOST` | `PGHOST` |
| `DB_PORT` | `PGPORT` |
| `DB_DATABASE` | `PGDATABASE` |
| `DB_USERNAME` | `PGUSER` |
| `DB_PASSWORD` | `PGPASSWORD` |

In Railway UI: **Variables** → **+ New Variable** → **Variable Reference** → Service: **Postgres** → pick the column on the right.

Also set (plain text on web service):

```
DB_CONNECTION=pgsql
```

Do **not** set `DATABASE_URL` or `DATABASE_PRIVATE_URL` when using individual `DB_*` refs (remove them if present).

The app also accepts **`PGHOST`** etc. directly if you reference those names on the web service instead of `DB_*` (see `config/database.php`).

#### If connection still fails

- Use **`DATABASE_PRIVATE_URL`** (internal), not `DATABASE_PUBLIC_URL`, for app + DB in the same project.
- Try adding: `DB_SSLMODE=require` (some public Postgres endpoints need SSL).
- Redeploy after changing variables (`php artisan config:clear` runs via container rebuild).

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
| `APP_URL` | `https://renpresso.com` (canonical — emails, default links) |
| `APP_ALLOWED_HOSTS` | `renpresso.com,www.renpresso.com,renpresso-production.up.railway.app` |

With DNS live, keep **`APP_URL=https://renpresso.com`**. Add **`APP_ALLOWED_HOSTS`** so the app also works on the Railway hostname (forms, redirects, login use whichever host you opened).
| `DB_CONNECTION` | `pgsql` |

PostgreSQL: see **§2 PostgreSQL** above (`DATABASE_PRIVATE_URL` or `DB_*` references). Do not leave `DB_HOST=127.0.0.1`.

**Without Redis** (default — required unless you add a Redis service):

```
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

If these are missing and `REDIS_URL` is not set, `docker/entrypoint.sh` sets them automatically on boot.  
Symptom when misconfigured: **`/up` returns 200 but every other page returns 500** (Redis connection failure).

**With Redis** (optional):

```
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_CLIENT=phpredis
```

Plus Redis host/password from the Redis plugin.

### 5. Deploy

Push to `main` — Railway redeploys automatically. First deploy runs migrations via `docker/entrypoint.sh`.

**Database is empty after first deploy** — migrations create tables but do not load demo data. To populate the public listings directory:

```bash
railway login
railway link
railway run php artisan db:seed --class=PublicListingSeeder --force
```

This adds sample **public** US listings (safe to re-run; skips duplicates). Full local demo data:

```bash
php artisan db:seed --force
php artisan db:seed --class=MultiUnitDemoSeeder --force
```

Real signups, waitlist entries, and landlord-created properties appear only after users use the live site — they are not copied from your local machine.

### CLI (optional)

```bash
railway login
railway link
railway up
```

Same Railway/Docker setup as the legacy RentersMaxx stack — deploy root is this repo.
