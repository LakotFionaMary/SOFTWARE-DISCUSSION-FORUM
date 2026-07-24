FROM php:8.4-fpm-bookworm

# System deps + PHP extension build deps (Debian equivalents of the
# previous Alpine/apk packages). PHPIZE_DEPS is set by the base image on
# both Alpine and Debian variants, so it's used the same way here.
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm \
    python3 \
    python3-pip \
    python3-dev \
    gcc \
    g++ \
    gfortran \
    libopenblas-dev \
    $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pcntl pdo pdo_mysql bcmath gd \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY . .

# Copy and install Python ML dependencies - now resolves to prebuilt
# manylinux (glibc) wheels instead of compiling from source
RUN if [ -f ml_service/requirements.txt ]; then pip3 install --no-cache-dir --break-system-packages -r ml_service/requirements.txt; fi

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/app/public
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-interaction --optimize-autoloader --no-dev

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PYTHONUNBUFFERED=1
ENV ML_SERVICE_URL="http://127.0.0.1:5001"
RUN composer install --no-interaction --optimize-autoloader --no-dev

# =========================================================
# 1. Accept Build Arguments from Railway
# =========================================================
ARG VITE_REVERB_APP_KEY
ARG VITE_REVERB_HOST
ARG VITE_REVERB_PORT
ARG VITE_REVERB_SCHEME
# =========================================================
# 2. Expose them to Node/Vite during "npm run build"
# =========================================================
ENV VITE_REVERB_APP_KEY=$VITE_REVERB_APP_KEY
ENV VITE_REVERB_HOST=$VITE_REVERB_HOST
ENV VITE_REVERB_PORT=$VITE_REVERB_PORT
ENV VITE_REVERB_SCHEME=$VITE_REVERB_SCHEME
RUN npm ci && npm run build

EXPOSE 8080
ENV PHP_CLI_SERVER_WORKERS=4
RUN chmod +x docker-entrypoint.sh
CMD ["./docker-entrypoint.sh"]
