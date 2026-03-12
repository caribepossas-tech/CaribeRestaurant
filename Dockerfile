# Step 1: Base image with PHP extensions
FROM php:8.3-fpm-alpine AS base

# Install runtime and build dependencies
RUN apk add --no-cache \
    libpng \
    libjpeg-turbo \
    freetype \
    icu-libs \
    libzip \
    libxml2 \
    oniguruma \
    git \
    unzip \
    && apk add --no-cache --virtual .build-deps \
    libpng-dev \
    libxml2-dev \
    oniguruma-dev \
    icu-dev \
    libzip-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    linux-headers \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    intl \
    zip \
    && apk del .build-deps

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Step 2: PHP Dependencies
FROM base AS vendor

WORKDIR /var/www/html

# Copy composer files and install
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Step 3: Frontend Assets
FROM node:20-alpine AS frontend

WORKDIR /var/www/html

# Copy package files and install
COPY package.json package-lock.json ./
RUN npm install

# Copy source and build
COPY . .
RUN npm run build

# Step 4: Final Production Image
FROM base

WORKDIR /var/www/html

# Install runtime dependencies (nginx and supervisor)
RUN apk add --no-cache \
    nginx \
    supervisor

# Copy application files
COPY --from=vendor /var/www/html/vendor ./vendor
COPY --from=frontend /var/www/html/public/build ./public/build
COPY . .

# Copy configurations
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/uploads.ini
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Fix permissions and setup cron
RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache /var/log/supervisor \
    && chmod +x /usr/local/bin/entrypoint.sh \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && echo "* * * * * /usr/local/bin/php /var/www/html/artisan schedule:run >> /dev/null 2>&1" > /var/spool/cron/crontabs/www-data

# Generate optimized autoloader after full copy
RUN /usr/bin/composer dump-autoload --optimize --no-dev

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
