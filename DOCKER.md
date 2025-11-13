# Docker Setup Guide - UMKM.go

Panduan lengkap untuk menjalankan aplikasi UMKM.go menggunakan Docker.

## 📋 Prerequisites

- Docker Desktop untuk Windows/Mac atau Docker Engine untuk Linux
- Docker Compose (sudah include di Docker Desktop)
- Git

## 🚀 Quick Start

### 1. Clone Repository

```bash
git clone <repository-url>
cd umkm-app
```

### 2. Setup Environment

Buat file `.env` dari `.env.example` atau gunakan konfigurasi Docker:

```bash
cp .env.example .env
```

Atau edit `.env` dan sesuaikan untuk Docker:

```env
APP_NAME="UMKM Katalog"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=umkm
DB_USERNAME=umkm_user
DB_PASSWORD=umkm_password

# AI Service
AI_SERVICE_URL=https://ai-martabakmanis-production.up.railway.app
```

### 3. Build dan Start Containers

```bash
# Build images
docker-compose build

# Start containers
docker-compose up -d

# Atau build dan start sekaligus
docker-compose up -d --build
```

### 4. Install Dependencies dan Setup Database

```bash
# Masuk ke container app
docker-compose exec app bash

# Install PHP dependencies
composer install

# Install Node dependencies (dilakukan otomatis di node container)
# Tapi jika perlu manual:
docker-compose exec node npm install

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Build assets (Production)
docker-compose exec node npm run build

# Atau run dev server (Development)
docker-compose exec node npm run dev
```

### 5. Akses Aplikasi

- **Web Application**: http://localhost:8000
- **Vite Dev Server**: http://localhost:5173 (jika dev mode)
- **MySQL**: localhost:3306
  - Username: `umkm_user`
  - Password: `umkm_password`
  - Database: `umkm`

## 📝 Perintah Docker Yang Sering Digunakan

### Menjalankan Container

```bash
# Start containers
docker-compose up -d

# Start dengan melihat logs
docker-compose up

# Stop containers
docker-compose stop

# Stop dan remove containers
docker-compose down

# Stop, remove containers dan volumes
docker-compose down -v
```

### Masuk ke Container

```bash
# Masuk ke container app (PHP/Laravel)
docker-compose exec app bash

# Masuk ke container node
docker-compose exec node sh

# Masuk ke MySQL
docker-compose exec mysql mysql -u umkm_user -pumkm_password umkm
```

### Artisan Commands

```bash
# Run artisan commands
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
```

### NPM Commands

```bash
# Install dependencies
docker-compose exec node npm install

# Build for production
docker-compose exec node npm run build

# Run dev server
docker-compose exec node npm run dev
```

### Database Commands

```bash
# Masuk ke MySQL CLI
docker-compose exec mysql mysql -u umkm_user -pumkm_password umkm

# Backup database
docker-compose exec mysql mysqldump -u umkm_user -pumkm_password umkm > backup.sql

# Restore database
docker-compose exec -T mysql mysql -u umkm_user -pumkm_password umkm < backup.sql
```

### Logs

```bash
# View all logs
docker-compose logs

# View logs dari service tertentu
docker-compose logs app
docker-compose logs mysql
docker-compose logs nginx
docker-compose logs node

# Follow logs (real-time)
docker-compose logs -f app
```

## 🔧 Konfigurasi

### Mengubah Port

Edit `docker-compose.yml`:

```yaml
services:
  nginx:
    ports:
      - "8080:80"  # Ubah 8080 ke port yang diinginkan
  
  mysql:
    ports:
      - "3307:3306"  # Ubah 3307 ke port yang diinginkan
```

### Mengubah Password Database

Edit `docker-compose.yml` dan `.env`:

```yaml
services:
  mysql:
    environment:
      MYSQL_ROOT_PASSWORD: your_root_password
      MYSQL_PASSWORD: your_password
```

### Menambahkan Volume untuk Storage

Jika ingin persist storage files:

```yaml
services:
  app:
    volumes:
      - ./storage/app/public:/var/www/html/storage/app/public
```

## 🐛 Troubleshooting

### Container tidak bisa start

```bash
# Cek status containers
docker-compose ps

# Cek logs
docker-compose logs

# Rebuild containers
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Permission Error

```bash
# Fix permissions
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
docker-compose exec app chmod -R 775 /var/www/html/storage
docker-compose exec app chmod -R 775 /var/www/html/bootstrap/cache
```

### Database Connection Error

```bash
# Pastikan MySQL container running
docker-compose ps mysql

# Test koneksi
docker-compose exec app php artisan tinker
# Di tinker: DB::connection()->getPdo();
```

### Clear All Cache

```bash
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
```

### Reset Database

```bash
# Hapus semua data dan re-run migrations
docker-compose exec app php artisan migrate:fresh --seed
```

## 📦 Production Deployment

Untuk production, buat file `docker-compose.prod.yml`:

```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
      target: production
    environment:
      APP_ENV=production
      APP_DEBUG=false
      # ... environment variables lainnya
  
  nginx:
    # Nginx configuration untuk production
    volumes:
      - ./docker/nginx/prod.conf:/etc/nginx/conf.d/default.conf
```

Run production:

```bash
docker-compose -f docker-compose.prod.yml up -d
```

## 🔐 Default Credentials (Setelah Seeder)

- **Admin**: `admin@umkm.com` / `password`
- **UMKM**: `umkm@example.com` / `password`
- **User**: `user@example.com` / `password`

## 📚 Struktur Docker

```
umkm-app/
├── Dockerfile              # PHP-FPM image
├── docker-compose.yml      # Docker Compose configuration
├── docker-entrypoint.sh    # Entrypoint script
├── .dockerignore           # Files to ignore in Docker build
└── docker/
    ├── nginx/
    │   └── default.conf    # Nginx configuration
    ├── php/
    │   └── local.ini       # PHP configuration
    └── Dockerfile.dev      # Development Dockerfile
```

## 🌐 Akses dari Mobile Device

Setelah container running, aplikasi dapat diakses dari mobile device di jaringan yang sama:

1. **Cek IP address container/host:**
   ```bash
   docker inspect umkm_nginx | grep IPAddress
   # atau gunakan IP komputer Anda
   ```

2. **Akses dari HP:**
   ```
   http://<IP-ADDRESS>:8000
   ```

3. **Untuk Vite dev server:**
   Edit `docker-compose.yml` untuk node service:
   ```yaml
   node:
     command: sh -c "npm install && npm run dev -- --host 0.0.0.0"
   ```

## 📝 Notes

- Database data disimpan di volume `mysql_data`, jadi data tetap ada meski container dihapus
- Untuk development, gunakan `npm run dev` di node container
- Untuk production, build assets dengan `npm run build`
- Pastikan port 8000 dan 3306 tidak digunakan aplikasi lain

## 🆘 Support

Jika mengalami masalah:
1. Cek logs: `docker-compose logs`
2. Cek status: `docker-compose ps`
3. Restart containers: `docker-compose restart`
4. Rebuild: `docker-compose down && docker-compose up -d --build`

---

**Happy Coding! 🚀**

