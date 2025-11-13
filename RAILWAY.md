# 🚂 Railway Deployment - Quick Guide

## Deploy ke Railway dengan Dockerfile

### 1. Setup di Railway Dashboard

1. **Create New Project** → Deploy from GitHub repo
2. **Add MySQL Database** → New → Database → Add MySQL
3. **Link Database** ke service aplikasi

### 2. Environment Variables

Di Railway Dashboard → Service → Variables, tambahkan:

```env
APP_NAME="UMKM Katalog"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app-name.up.railway.app
APP_KEY=base64:...  # Generate setelah deploy pertama

# Database (Gunakan Railway Variables)
DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

SESSION_DRIVER=database
CACHE_DRIVER=database
FILESYSTEM_DISK=public
```

### 3. Setup Setelah Deploy

Di Railway Shell (Service → Deploy Logs → Shell):

```bash
# Generate APP_KEY
php artisan key:generate

# Run migrations
php artisan migrate --force
php artisan db:seed --force

# Cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 4. Fix 502 Bad Gateway

Pastikan:
- ✅ `APP_KEY` sudah di-generate
- ✅ Database variables menggunakan `${{MySQL.*}}`
- ✅ `APP_URL` sesuai Railway domain
- ✅ Migrations sudah dijalankan

---

**Dockerfile sudah dikonfigurasi untuk Railway!** 🚀

