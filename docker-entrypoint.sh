#!/bin/sh
set -e

echo ">>> Clearing config/cache..."
php artisan config:clear
php artisan cache:clear

echo ">>> Running migrations..."
php artisan migrate --force

echo ">>> Starting ML service..."
python3 ml_service/app.py &

echo ">>> Starting queue worker..."
php artisan queue:work --tries=3 --sleep=1 &

echo ">>> Starting web server on port ${PORT:-8080}..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
