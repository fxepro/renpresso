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

## Deploy

Same Railway/Docker setup as RentersMaxx — point deploy root to this directory when cutting over.
