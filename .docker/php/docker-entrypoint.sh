#!/bin/sh
set -e

echo "Ensure storage directories exist..."
mkdir -p /var/www/html/storage/framework/{views,cache,sessions}
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copy .env.example to .env if not present
if [ ! -f /var/www/html/.env ]; then
    echo ".env not found; copying .env.example → .env"
    cp /var/www/html/.env.example /var/www/html/.env
else
    echo ".env exists; skipping copy"
fi

echo "Composer install..."
if [ "$APP_ENV" = "production" ]; then
    composer install --prefer-dist --no-interaction --no-progress --optimize-autoloader --no-dev
else
    composer install --prefer-dist --no-interaction --no-progress --optimize-autoloader
fi

# Generate APP_KEY if not set
if ! grep -q "^APP_KEY=base64:" /var/www/html/.env; then
    echo "APP_KEY is empty, generating new key..."
    php artisan key:generate --force
else
    echo "APP_KEY exists; skipping generation"
fi

echo "Checking Redis connection..."
REDIS_MESSAGE=$(redis-cli -h "${REDIS_HOST:-booking_microservice_redis}" ping 2>/dev/null || true)
if [ "$REDIS_MESSAGE" = "PONG" ]; then
    echo "Successfully connected to Redis."
else
    echo "Warning: Failed to connect to Redis."
fi

if [ ! -L "/var/www/html/public/storage" ]; then
    echo "Creating storage link..."
    php artisan storage:link
else
    echo "Storage link exists; skipping creation"
fi

echo "Running migrations..."
php artisan migrate --force

echo "Running seeders..."
php artisan db:install
php artisan db:seed --force

if [ "$APP_ENV" = "production" ]; then
    echo "Caching configuration, routes, and views..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
else
    echo "Clearing caches for development..."
    php artisan optimize:clear
fi

chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Execute the main container command
exec "$@"
