FROM php:8.4-fpm-alpine

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx \
    nodejs \
    npm

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

# Build frontend assets for production (Vite / Chart.js)
RUN npm ci
RUN npm run build

# Force absolute environment connection overrides
ENV DB_CONNECTION=mysql
ENV DB_HOST=${MYSQLHOST}
ENV DB_PORT=${MYSQLPORT}
ENV DB_DATABASE=${MYSQLDATABASE}
ENV DB_USERNAME=${MYSQLUSER}
ENV DB_PASSWORD=${MYSQLPASSWORD}

# Setup entrypoint port binding and clear runtime caches
EXPOSE 8080
CMD php artisan config:clear && php artisan cache:clear && php artisan serve --host=0.0.0.0 --port=8080
