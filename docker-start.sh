#!/bin/bash

echo "========================================"
echo "UMKM.go - Docker Setup"
echo "========================================"
echo ""

# Check if .env exists
if [ ! -f ".env" ]; then
    echo "⚠️  .env file not found. Copying from .env.example..."
    cp .env.example .env
    echo "✅ .env file created"
else
    echo "✅ .env file exists"
fi

# Build and start containers
echo ""
echo "🐳 Building and starting Docker containers..."
docker-compose up -d --build

# Wait for MySQL to be ready
echo ""
echo "⏳ Waiting for MySQL to be ready..."
sleep 10

# Install PHP dependencies
echo ""
echo "📦 Installing PHP dependencies..."
docker-compose exec -T app composer install --no-interaction --prefer-dist --optimize-autoloader

# Generate key if not exists
echo ""
echo "🔑 Generating application key..."
docker-compose exec -T app php artisan key:generate --force

# Run migrations
echo ""
echo "🗄️  Running database migrations..."
docker-compose exec -T app php artisan migrate --force

# Seed database
echo ""
echo "🌱 Seeding database..."
docker-compose exec -T app php artisan db:seed --force

# Clear caches
echo ""
echo "🧹 Clearing caches..."
docker-compose exec -T app php artisan config:clear
docker-compose exec -T app php artisan cache:clear
docker-compose exec -T app php artisan route:clear
docker-compose exec -T app php artisan view:clear

# Install Node dependencies
echo ""
echo "📦 Installing Node.js dependencies..."
docker-compose exec -T node npm install

# Set permissions
echo ""
echo "🔒 Setting permissions..."
docker-compose exec -T app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
docker-compose exec -T app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo ""
echo "========================================"
echo "✅ Setup Complete!"
echo "========================================"
echo ""
echo "🌐 Web Application: http://localhost:8000"
echo "⚡ Vite Dev Server: http://localhost:5173"
echo ""
echo "📝 Default Accounts:"
echo "   Admin: admin@umkm.com / password"
echo "   UMKM: umkm@example.com / password"
echo "   User: user@example.com / password"
echo ""
echo "🔧 Useful Commands:"
echo "   docker-compose logs -f        # View logs"
echo "   docker-compose exec app bash  # Enter app container"
echo "   docker-compose down           # Stop containers"
echo ""
echo "🚀 To build assets for production:"
echo "   docker-compose exec node npm run build"
echo ""
echo "💡 Or run dev server:"
echo "   docker-compose exec node npm run dev"
echo ""

