FROM php:8.4-fpm-alpine

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx

RUN docker-php-ext-install pdo pdo_mysql bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory and copy project files
WORKDIR /app
COPY . .

# Create missing Laravel framework storage cache directories
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/app/public
RUN chmod -R 777 storage bootstrap/cache

# Install dependencies using production flags
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Setup entrypoint port binding
EXPOSE 80
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-80}

