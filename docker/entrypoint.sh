#!/bin/bash
set -e

# Install vendor dependencies if missing (named volume may be empty on first run)
if [ ! -f /var/www/vendor/autoload.php ]; then
    echo "Installing Composer dependencies..."
    composer config audit.block-insecure false
    composer install --no-interaction --no-dev --optimize-autoloader --no-scripts
    composer dump-autoload --no-scripts --optimize
fi

# Generate app key if not set
if ! grep -q "^APP_KEY=base64:" /var/www/.env 2>/dev/null; then
    php artisan key:generate --force
fi

# Run package discovery
php artisan package:discover --ansi 2>/dev/null || true

# Clear caches for fresh start
php artisan config:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

exec "$@"
