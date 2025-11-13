#!/bin/sh

echo "🚀 Starting Laravel Application..."

# Set permissions (non-blocking)
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

# Setup storage link (non-blocking)
php artisan storage:link 2>/dev/null || true

# Clear all caches first (non-blocking)
php artisan config:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

# Generate APP_KEY if not set
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    echo "🔑 Generating APP_KEY..."
    php artisan key:generate --force 2>&1 || echo "⚠️  Warning: Could not generate APP_KEY"
fi

# Start Laravel server
echo "✅ Starting server on port ${PORT:-8000}..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
