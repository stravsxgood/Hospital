# ==============================================================================
# Stage 1: Build Frontend & Backend Dependencies
# ==============================================================================
FROM php:8.4-fpm-alpine AS builder

WORKDIR /var/www/html

# Environment variables
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1 \
    NODE_ENV=production

# Install build dependencies, PHP extensions, and Node.js for Vite/Wayfinder
RUN apk add --no-cache \
    bash \
    git \
    unzip \
    zip \
    nodejs \
    npm \
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
        zip

# Copy Composer binary
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install PHP dependencies first (cached layer)
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Install Node.js dependencies
COPY package.json package-lock.json* pnpm-lock.yaml* ./
RUN npm ci --include=dev

# Copy application source code
COPY . .

# Generate dummy .env if missing for Artisan / Wayfinder route discovery during build
RUN [ -f .env ] || cp .env.example .env && php artisan key:generate --force || true

# Run Wayfinder type generation & Vite production build
RUN npm run build

# Optimize autoload mappings
RUN composer dump-autoload --optimize --no-dev

# ==============================================================================
# Stage 2: Production Runtime (PHP 8.4-FPM + Nginx + Supervisor)
# ==============================================================================
FROM php:8.4-fpm-alpine AS runner

WORKDIR /var/www/html

ENV NODE_ENV=production

# Install runtime libraries, PHP extensions, Nginx, and Supervisor
RUN apk add --no-cache \
    bash \
    curl \
    nginx \
    supervisor \
    icu-libs \
    libzip \
    libpng \
    libjpeg-turbo \
    freetype \
    postgresql-libs \
    oniguruma \
    $PHPIZE_DEPS \
    icu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    postgresql-dev \
    oniguruma-dev \
    linux-headers \
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
    && apk del --no-cache $PHPIZE_DEPS icu-dev libzip-dev libpng-dev libjpeg-turbo-dev freetype-dev postgresql-dev oniguruma-dev linux-headers \
    && rm -rf /tmp/pear

# Copy Composer binary to container
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy built application and vendor from builder stage
COPY --from=builder /var/www/html /var/www/html

# Copy Nginx & Supervisor configuration files
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Set directory permissions for Laravel storage and cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose HTTP (80), Reverb WebSockets (8080), and PHP-FPM (9000)
EXPOSE 80 8080 9000

# Set entrypoint and default start command
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
