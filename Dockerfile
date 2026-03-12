# Step 1: PHP Dependencies
FROM php:8.3-fpm-alpine AS vendor

WORKDIR /var/www/html

# Install system dependencies
RUN apk add --no-cache \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    oniguruma-dev \
    icu-dev \
    libzip-dev \
    libjpeg-turbo-dev \
    freetype-dev

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    intl \
    zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer files and install
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Step 2: Frontend Assets
FROM node:20-alpine AS frontend

WORKDIR /var/www/html

# Copy package files and install
COPY package.json package-lock.json ./
RUN npm install

# Copy source and build
COPY . .
RUN npm run build

# Step 3: Final Production Image
FROM php:8.3-fpm-alpine

WORKDIR /var/www/html

# Install runtime dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    libpng \
    libjpeg-turbo \
    freetype \
    icu-libs \
    libzip \
    libxml2 \
    oniguruma

# Install PHP extensions for runtime
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    intl \
    zip

# Copy application files
COPY --from=vendor /var/www/html/vendor ./vendor
COPY --from=frontend /var/www/html/public/build ./public/build
COPY . .

# Copy configurations
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Fix permissions
RUN chmod +x /usr/local/bin/entrypoint.sh \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Generate optimized autoloader after full copy
RUN /usr/bin/composer dump-autoload --optimize --no-dev

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
