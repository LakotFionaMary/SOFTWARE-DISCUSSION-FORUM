FROM php:8.4-fpm-alpine

# Install build dependencies and PHP extensions in one layer
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && docker-php-ext-install pcntl pdo pdo_mysql bcmath gd \
    && apk del .build-deps

# Install runtime system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# Create required Laravel storage directories
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/app/public

# Set correct ownership and permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Install PHP dependencies
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Build frontend assets
RUN npm ci && npm run build

EXPOSE 8080

CMD php artisan config:clear && php artisan cache:clear && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
