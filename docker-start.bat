@echo off
echo ========================================
echo UMKM.go - Docker Setup
echo ========================================
echo.

REM Check if .env exists
if not exist ".env" (
    echo .env file not found. Copying from .env.example...
    copy .env.example .env
    echo .env file created
) else (
    echo .env file exists
)

echo.
echo Building and starting Docker containers...
docker-compose up -d --build

echo.
echo Waiting for MySQL to be ready...
timeout /t 10 /nobreak >nul

echo.
echo Installing PHP dependencies...
docker-compose exec -T app composer install --no-interaction --prefer-dist --optimize-autoloader

echo.
echo Generating application key...
docker-compose exec -T app php artisan key:generate --force

echo.
echo Running database migrations...
docker-compose exec -T app php artisan migrate --force

echo.
echo Seeding database...
docker-compose exec -T app php artisan db:seed --force

echo.
echo Clearing caches...
docker-compose exec -T app php artisan config:clear
docker-compose exec -T app php artisan cache:clear
docker-compose exec -T app php artisan route:clear
docker-compose exec -T app php artisan view:clear

echo.
echo Installing Node.js dependencies...
docker-compose exec -T node npm install

echo.
echo Setting permissions...
docker-compose exec -T app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
docker-compose exec -T app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo.
echo ========================================
echo Setup Complete!
echo ========================================
echo.
echo Web Application: http://localhost:8000
echo Vite Dev Server: http://localhost:5173
echo.
echo Default Accounts:
echo    Admin: admin@umkm.com / password
echo    UMKM: umkm@example.com / password
echo    User: user@example.com / password
echo.
echo Useful Commands:
echo    docker-compose logs -f        # View logs
echo    docker-compose exec app bash  # Enter app container
echo    docker-compose down           # Stop containers
echo.
echo To build assets for production:
echo    docker-compose exec node npm run build
echo.
echo Or run dev server:
echo    docker-compose exec node npm run dev
echo.
pause

