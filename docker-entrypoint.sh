#!/bin/sh

echo "🚀 Starting Laravel Application..."

# Set permissions (non-blocking)
echo "🔒 Setting permissions..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

# Clear all caches first (important before generating APP_KEY)
echo "🧹 Clearing all caches..."
php artisan config:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

# Generate APP_KEY if not set (must be done after config:clear)
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    echo "🔑 Generating APP_KEY..."
    if php artisan key:generate --force; then
        echo "✅ APP_KEY generated successfully!"
    else
        echo "⚠️  Warning: Could not generate APP_KEY"
    fi
else
    echo "✅ APP_KEY already set"
fi

# Setup storage link
echo "📁 Setting up storage link..."
php artisan storage:link 2>/dev/null || true

# Wait for database to be ready and run migrations
if [ -n "$DB_HOST" ]; then
    echo "⏳ Waiting for database connection..."
    echo "   DB_HOST: $DB_HOST"
    echo "   DB_PORT: ${DB_PORT:-3306}"
    echo "   DB_DATABASE: $DB_DATABASE"
    echo "   DB_USERNAME: $DB_USERNAME"
    
    max_attempts=60
    attempt=0
    db_connected=0
    
    while [ $attempt -lt $max_attempts ]; do
        # Test database connection with PDO (more reliable)
        if php -r "
        try {
            \$pdo = new PDO('mysql:host=${DB_HOST};port=${DB_PORT:-3306}', '${DB_USERNAME}', '${DB_PASSWORD}');
            \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            \$pdo->exec('USE ${DB_DATABASE}');
            exit(0);
        } catch (Exception \$e) {
            exit(1);
        }
        " 2>/dev/null; then
            echo "✅ Database connected!"
            db_connected=1
            break
        fi
        attempt=$((attempt + 1))
        if [ $((attempt % 10)) -eq 0 ]; then
            echo "⏳ Still waiting for database... ($attempt/$max_attempts)"
        fi
        sleep 1
    done
    
    if [ $db_connected -eq 1 ]; then
        # Run migrations (show output for debugging)
        echo "📊 Running migrations..."
        if php artisan migrate --force; then
            echo "✅ Migrations completed successfully!"
            
            # Run seeders (only if RUN_SEEDERS is set to true or not set)
            if [ "${RUN_SEEDERS:-true}" = "true" ]; then
                echo "🌱 Running seeders..."
                if php artisan db:seed --force; then
                    echo "✅ Seeders completed successfully!"
                else
                    echo "⚠️  Warning: Seeders failed!"
                    echo "⚠️  Check the error message above for details"
                    echo "⚠️  Continuing anyway..."
                fi
            else
                echo "⏭️  Skipping seeders (RUN_SEEDERS=false)"
            fi
        else
            echo "❌ ERROR: Migrations failed!"
            echo "❌ Check the error message above for details"
            echo "⚠️  Continuing anyway, but application may not work correctly..."
        fi
    else
        echo "⚠️  Warning: Could not connect to database after $max_attempts attempts"
        echo "⚠️  Check database configuration:"
        echo "   DB_HOST: $DB_HOST"
        echo "   DB_PORT: ${DB_PORT:-3306}"
        echo "   DB_DATABASE: $DB_DATABASE"
        echo "   DB_USERNAME: $DB_USERNAME"
        echo "⚠️  Continuing anyway, but migrations may fail..."
    fi
else
    echo "⚠️  Warning: DB_HOST not set, skipping database setup..."
fi

# Cache config for production (optional, can be skipped if APP_ENV is local)
if [ "${APP_ENV:-local}" = "production" ]; then
    echo "💾 Caching configuration for production..."
    php artisan config:cache 2>/dev/null || true
    php artisan route:cache 2>/dev/null || true
    php artisan view:cache 2>/dev/null || true
else
    echo "🔧 Development mode: skipping config cache"
fi

# Start Laravel server
echo "✅ Starting server on port ${PORT:-8000}..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
