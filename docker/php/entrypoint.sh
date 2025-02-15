#!/bin/sh
set -e

# Check if $UID is set, else fallback to default (1000)
USER_ID=${UID:-1000}

# Fix file ownership and permissions using the passed UID
echo "Fixing file permissions with UID=${USER_ID}..."
chown -R "${USER_ID}":"${USER_ID}" /var/www/app || echo "Some files could not be changed"

# Clear configurations to avoid caching issues in development
echo "Clearing configurations..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Run the default command (e.g., php-fpm or bash)
exec "$@"
