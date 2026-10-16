#!/bin/bash

set -e

PROJECT_DIR="${PROJECT_DIR:-/var/www/ticket-supporting}"
PHP_BIN="/usr/bin/php"
COMPOSER_BIN="/usr/bin/composer"
NPM_BIN="/usr/bin/npm"
GIT_BIN="/usr/bin/git"
BRANCH="main"

echo "===== DEPLOY START ====="
cd "$PROJECT_DIR"

echo "-> Pull latest code from GitHub"
$GIT_BIN pull origin $BRANCH

echo "-> Install/update PHP dependencies"
$COMPOSER_BIN install --no-dev --optimize-autoloader

echo "-> Run database migrations"
$PHP_BIN artisan migrate --force

echo "-> Clear and rebuild Laravel caches"
$PHP_BIN artisan optimize:clear
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache

if [ -f "package.json" ]; then
    echo "-> package.json detected"
    if command -v npm >/dev/null 2>&1; then
        echo "-> Install/update Node dependencies"
        $NPM_BIN install

        echo "-> Build frontend assets"
        $NPM_BIN run build
    else
        echo "-> npm not found, skipping frontend build"
    fi
else
    echo "-> No package.json found, skipping frontend build"
fi

echo "-> Restart queue workers"
$PHP_BIN artisan queue:restart || true

echo "===== DEPLOY COMPLETE ====="