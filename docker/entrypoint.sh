#!/bin/sh
set -e

# Generate .env file from environment variables for compatibility
echo "Generating .env file from environment variables..."
env | grep -E '^APP_|^DB_|^MAIL_|^REDIS_|^CACHE_|^SESSION_|^QUEUE_|^LOG_|^SERVICES_|^MIX_|^VITE_|^PUSHER_|^RAZORPAY_|^STRIPE_|^PAYPAL_|^REDIRECT_HTTPS' > /var/www/html/.env

# Ensure storage and cache directories exist and are writable
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

# Cache configuration, routes and views
if [ "$APP_ENV" = "production" ]; then
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
