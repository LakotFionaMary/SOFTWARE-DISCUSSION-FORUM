FROM php:8.4-fpm-bookworm
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

# Install Python ML dependencies
RUN if [ -f ml_service/requirements.txt ]; then pip3 install --no-cache-dir --break-system-packages -r ml_service/requirements.txt; fi

# NEW: Train the ML classifier models at build time, so classifier.pkl
# and vectorizer.pkl are baked into the image instead of relying on
# training happening inside the running container.
RUN cd ml_service && python3 merge_datasets.py && python3 train_classifier.py

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/app/public
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PYTHONUNBUFFERED=1
RUN composer install --no-interaction --optimize-autoloader --no-dev
ARG VITE_REVERB_APP_KEY
ARG VITE_REVERB_HOST
ARG VITE_REVERB_PORT
ARG VITE_REVERB_SCHEME
ENV VITE_REVERB_APP_KEY=$VITE_REVERB_APP_KEY
ENV VITE_REVERB_HOST=$VITE_REVERB_HOST
ENV VITE_REVERB_PORT=$VITE_REVERB_PORT
ENV VITE_REVERB_SCHEME=$VITE_REVERB_SCHEME
RUN npm ci && npm run build
EXPOSE 8080
ENV PHP_CLI_SERVER_WORKERS=4
RUN chmod +x docker-entrypoint.sh
CMD ["./docker-entrypoint.sh"]
