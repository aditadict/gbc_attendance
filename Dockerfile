FROM php:8.4-cli-alpine

# System dependencies + build tools for pecl
RUN apk add --no-cache \
    bash \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    libzip-dev \
    libpq-dev \
    icu-dev \
    oniguruma-dev \
    su-exec \
    nodejs \
    npm \
    $PHPIZE_DEPS

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        gd \
        bcmath \
        mbstring \
        pcntl \
        intl \
        zip \
        opcache \
        exif

# Swoole for Octane (installed even if laravel/octane not in composer.json yet)
RUN pecl install swoole && docker-php-ext-enable swoole \
    && apk del $PHPIZE_DEPS

# opcache tuning for Octane
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP deps (layer-cached separately from source)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction

# Install Node deps (use ci for reproducible builds)
COPY package.json package-lock.json ./
RUN npm ci

# Copy application source
COPY . .

# Finalize autoloader + build frontend assets (including Filament themes)
RUN composer dump-autoload --optimize --no-interaction \
    && npm run build \
    && rm -rf node_modules

# Pre-create storage structure and fix permissions
RUN mkdir -p storage/app/public \
        storage/framework/sessions \
        storage/framework/views \
        storage/framework/cache/data \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8000
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
