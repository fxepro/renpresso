#!/bin/bash

# ═══════════════════════════════════════════════════════════════
# Rentersmaxx — Local Setup Script
# Run: bash setup.sh
# ═══════════════════════════════════════════════════════════════

set -e

echo ""
echo "🏠 Rentersmaxx — Local Setup"
echo "════════════════════════════"
echo ""

# 1. Check PHP
if ! command -v php &> /dev/null; then
    echo "❌ PHP not found. Install PHP 8.2+ first."
    echo "   macOS:  brew install php"
    echo "   Ubuntu: sudo apt install php8.3 php8.3-cli php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-pgsql php8.3-bcmath php8.3-sqlite3"
    exit 1
fi
echo "✓ PHP $(php -r 'echo PHP_VERSION;')"

# 2. Check Composer
if ! command -v composer &> /dev/null; then
    echo "❌ Composer not found. Install from https://getcomposer.org"
    exit 1
fi
echo "✓ Composer $(composer --version --no-ansi 2>/dev/null | head -1)"

# 3. Install PHP dependencies
echo ""
echo "📦 Installing PHP dependencies..."
composer install --no-interaction --prefer-dist 2>/dev/null

# 4. Set up .env
if [ ! -f .env ]; then
    cp .env.example .env
    echo "✓ .env created from .env.example"
fi

# 5. Generate app key
php artisan key:generate --ansi

# 6. Ask about database
echo ""
echo "🗄️  Database setup"
echo "   1) SQLite (easiest — no setup needed)"
echo "   2) PostgreSQL (production-grade)"
echo ""
read -p "Choose [1/2, default 1]: " DB_CHOICE
DB_CHOICE=${DB_CHOICE:-1}

if [ "$DB_CHOICE" = "1" ]; then
    # SQLite — update .env
    sed -i.bak 's/DB_CONNECTION=pgsql/DB_CONNECTION=sqlite/' .env
    sed -i.bak '/^DB_HOST/d' .env
    sed -i.bak '/^DB_PORT/d' .env
    sed -i.bak '/^DB_DATABASE/d' .env
    sed -i.bak '/^DB_USERNAME/d' .env
    sed -i.bak '/^DB_PASSWORD/d' .env
    touch database/database.sqlite
    echo "✓ SQLite configured"
else
    echo ""
    echo "Enter your PostgreSQL credentials:"
    read -p "  DB host [127.0.0.1]: " DB_HOST; DB_HOST=${DB_HOST:-127.0.0.1}
    read -p "  DB name [rentersmaxx]: " DB_NAME; DB_NAME=${DB_NAME:-rentersmaxx}
    read -p "  DB user [rentersmaxx]: " DB_USER; DB_USER=${DB_USER:-rentersmaxx}
    read -s -p "  DB password: " DB_PASS; echo ""

    sed -i.bak "s/DB_HOST=127.0.0.1/DB_HOST=$DB_HOST/" .env
    sed -i.bak "s/DB_DATABASE=rentersmaxx/DB_DATABASE=$DB_NAME/" .env
    sed -i.bak "s/DB_USERNAME=rentersmaxx/DB_USERNAME=$DB_USER/" .env
    sed -i.bak "s/DB_PASSWORD=/DB_PASSWORD=$DB_PASS/" .env
    echo "✓ PostgreSQL configured"
fi

# 7. Run migrations
echo ""
echo "🗄️  Running migrations..."
php artisan migrate --ansi

# 8. Create storage symlink
php artisan storage:link --ansi 2>/dev/null || true

# 9. Cache config for speed
php artisan config:cache --ansi
php artisan route:cache --ansi

# 10. Node / Vite (optional)
if command -v npm &> /dev/null; then
    echo ""
    echo "📦 Installing Node dependencies..."
    npm install --silent
    echo "✓ Node dependencies installed"
    echo "   Run 'npm run dev' in a separate terminal for hot-reload CSS/JS"
else
    echo ""
    echo "ℹ️  npm not found — CSS/JS served from resources/ directly (fine for local)"
fi

echo ""
echo "════════════════════════════════════════"
echo "✅ Setup complete!"
echo ""
echo "Start the server:"
echo "  php artisan serve"
echo ""
echo "Then open: http://localhost:8000"
echo ""
echo "Start queue worker (separate terminal):"
echo "  php artisan queue:work --queue=webhooks,payments,notifications,default"
echo "════════════════════════════════════════"
echo ""
