FROM php:8.2-cli

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    nodejs \
    npm \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install PHP dependencies (skip scripts yang memerlukan Laravel)
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev --no-scripts --ignore-platform-reqs

# Copy package.json for Node dependencies
COPY package.json package-lock.json* ./

# Install Node dependencies
RUN npm ci --prefer-offline --no-audit || npm install --prefer-offline --no-audit

# Copy application files
COPY . /var/www/html

# Run composer scripts after copying files (dengan APP_KEY dummy jika belum ada)
RUN APP_KEY=base64:dummykey123456789012345678901234567890123456789012345678901234567890 || true \
    php artisan package:discover --ansi || true \
    composer dump-autoload --optimize

# Build assets
RUN npm run build

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Expose port (Railway will set PORT dynamically via environment variable)
EXPOSE 8000

# Start Laravel server
CMD sh -c "php artisan storage:link || true && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"

