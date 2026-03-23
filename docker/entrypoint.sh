#!/bin/sh
set -e

# Generate .env file from environment variables
echo "Generating .env file from environment variables..."
env | grep -E '^(APP_|DB_|MAIL_|REDIS_|CACHE_|SESSION_|QUEUE_|LOG_|SERVICES_|VITE_|PUSHER_|STRIPE_|RAZORPAY_|PAYPAL_|REDIRECT_HTTPS|FILESYSTEM_|AWS_|MINIO_|DIGITALOCEAN_|WASABI_|MAIN_APPLICATION_SUBDOMAIN|SHORT_DOMAIN_NAME)' > /var/www/html/.env || true

# Ensure storage and cache directories exist and are writable
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache \
         /var/www/html/resources/views/modules/inventory

# Create storage symlink
php artisan storage:link --force 2>/dev/null || true

# Cache configuration, routes and views (skip only for local dev)
if [ "$APP_ENV" != "local" ]; then
    echo "Caching configuration..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
fi

# Run migrations if enabled
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "Running migrations..."
    php artisan migrate --force
fi

# Fix permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public/user-uploads

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
