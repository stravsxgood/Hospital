# ==============================================================================
# Stage 1: Build Frontend Assets (Vite + Vue 3)
# ==============================================================================
FROM node:22-alpine AS frontend
WORKDIR /app

# Copy package manifests and install dependencies
COPY package.json package-lock.json* pnpm-lock.yaml* ./
RUN npm ci || npm install

# Copy application source code for asset building
COPY . .
RUN npm run build

# ==============================================================================
# Stage 2: Production PHP Runtime (PHP 8.4-FPM Alpine)
# ==============================================================================
FROM php:8.4-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Environment variables
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1 \
    NODE_ENV=production

# Install system dependencies & build tools
RUN apk add --no-cache \
    bash \
    curl \
    git \
    unzip \
    zip \
    icu-dev \
    icu-libs \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    postgresql-dev \
    postgresql-libs \
    oniguruma-dev \
    linux-headers \
    $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        gd \
        intl \
        opcache \
        pcntl \
        pdo_pgsql \
        pgsql \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del --no-cache $PHPIZE_DEPS \
    && rm -rf /tmp/pear

# Install Composer binary from official Composer image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy composer dependency manifests first (leveraging Docker layer cache)
COPY composer.json composer.lock ./

# Install production PHP dependencies without dev packages or post-scripts
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Copy the rest of the application codebase
COPY . .

# Copy compiled frontend assets from frontend stage
COPY --from=frontend /app/public/build ./public/build

# Run post-autoload dump & optimize
RUN composer dump-autoload --optimize --no-dev

# Set permissions for Laravel storage and cache directories
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose PHP-FPM default port
EXPOSE 9000

# Start PHP-FPM server
CMD ["php-fpm"]
