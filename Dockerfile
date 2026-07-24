FROM php:8.4-fpm-alpine

# Added python3, py3-pip, and C build tools for Pandas/Scikit-Learn
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm \
    python3 \
    py3-pip \
    python3-dev \
    gcc \
    g++ \
    gfortran \
    openblas-dev \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pcntl pdo pdo_mysql bcmath gd \
    && apk del .build-deps

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# Copy and install Python ML dependencies

RUN if [ -f ml_service/requirements.txt ]; then pip3 install --no-cache-dir --break-system-packages -r ml_service/requirements.txt; fi

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/app/public

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

ENV COMPOSER_ALLOW_SUPERUSER=1
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

CMD ["sh", "-c", "python3 ml_service/app.py & php artisan queue:work --daemon & php artisan config:clear && php artisan cache:clear && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"]


