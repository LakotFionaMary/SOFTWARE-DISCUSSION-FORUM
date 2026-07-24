#!/usr/bin/env bash
set -e

echo ">>> Clearing config/cache..."
php artisan config:clear
php artisan cache:clear

echo ">>> Running migrations..."
php artisan migrate --force

echo ">>> Starting ML service..."
python3 ml_service/app.py &
ML_PID=$!

echo ">>> Waiting for ML service to come up..."
i=0
until curl -sf "http://127.0.0.1:5001/health" > /dev/null 2>&1; do
  i=$((i + 1))
  if ! kill -0 "$ML_PID" 2>/dev/null; then
    echo "!!! ML service process died during startup"
    exit 1
  fi
  if [ "$i" -ge 30 ]; then
    echo "!!! ML service did not respond after 30s, continuing anyway"
    break
  fi
  sleep 1
done
echo ">>> ML service is up"

echo ">>> Starting queue worker..."
php artisan queue:work --tries=3 --sleep=1 &
QUEUE_PID=$!

echo ">>> Starting web server on port ${PORT:-8080}..."
php artisan serve --host=0.0.0.0 --port="${PORT:-8080}" &
WEB_PID=$!

trap 'kill -TERM $ML_PID $QUEUE_PID $WEB_PID 2>/dev/null' TERM INT

wait -n "$ML_PID" "$QUEUE_PID" "$WEB_PID"
EXIT_CODE=$?
echo ">>> One process exited ($EXIT_CODE), shutting down the rest..."
kill "$ML_PID" "$QUEUE_PID" "$WEB_PID" 2>/dev/null
exit "$EXIT_CODE"
