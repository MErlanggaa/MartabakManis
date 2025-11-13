# 🚀 Deployment Guide - Railway

Panduan deployment aplikasi UMKM.go ke Railway menggunakan Docker.

## 📋 Prerequisites

- Akun Railway (https://railway.app)
- Repository GitHub/GitLab
- MySQL Database di Railway

## 🚀 Quick Deploy

### 1. Push Code ke Repository

```bash
git add .
git commit -m "Deploy to Railway"
git push origin main
```

### 2. Connect Repository ke Railway

1. Login ke Railway Dashboard
2. New Project → Deploy from GitHub repo
3. Pilih repository Anda
4. Railway akan otomatis detect `Dockerfile`

### 3. Setup MySQL Database

1. Railway Dashboard → New → Database → MySQL
2. Railway akan otomatis create database
3. Link database ke service:
   - Service → Variables → Add Reference
   - Pilih MySQL database
   - Variables akan otomatis menggunakan `${{MySQL.*}}`

### 4. Set Environment Variables

Railway Dashboard → Service → Variables:

```env
APP_NAME=UMKM App
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app.railway.app
APP_KEY= (akan di-generate otomatis jika kosong)

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

LOG_CHANNEL=stack
LOG_LEVEL=error
RUN_SEEDERS=true (set false jika tidak ingin run seeders)
```

### 5. Deploy

Railway akan otomatis:
1. Build Docker image dari `Dockerfile`
2. Run entrypoint script yang akan:
   - ✅ Generate APP_KEY
   - ✅ Setup storage link
   - ✅ Clear semua caches
   - ✅ Wait for database connection (max 60 attempts)
   - ✅ Run migrations otomatis
   - ✅ Run seeders otomatis (jika `RUN_SEEDERS=true`)
   - ✅ Start Laravel server

## 🔍 Entrypoint Script

Entrypoint script (`docker-entrypoint.sh`) akan otomatis:

### 1. Setup Aplikasi
- Set permissions untuk storage dan bootstrap/cache
- Clear semua caches (config, cache, route, view)
- Generate APP_KEY jika belum ada
- Setup storage link

### 2. Database Setup
- Wait for database connection (max 60 attempts = 60 seconds)
- Test database connection dengan PDO
- Run migrations otomatis (`php artisan migrate --force`)
- Run seeders otomatis (`php artisan db:seed --force`) jika `RUN_SEEDERS=true`

### 3. Start Server
- Start Laravel server dengan port dinamis (`$PORT`)
- Server akan listen di `0.0.0.0:${PORT:-8000}`

## 📊 Migrations & Seeders

### Migrations
Entrypoint script akan otomatis run migrations:
```bash
php artisan migrate --force
```

### Seeders
Entrypoint script akan otomatis run seeders jika `RUN_SEEDERS=true`:
```bash
php artisan db:seed --force
```

**Default Seeders:**
- Admin user: `admin@umkm.com` / `password`
- UMKM user: `umkm@example.com` / `password`
- User: `user@example.com` / `password`
- Sample layanan data (8 items)

**Skip Seeders:**
Set `RUN_SEEDERS=false` di environment variables untuk skip seeders.

## 🔧 Troubleshooting

### 500 Server Error

1. **Cek Logs:**
   - Railway Dashboard → Service → Deploy Logs
   - Lihat error dari migrations atau seeders

2. **Cek Database Connection:**
   - Pastikan database sudah di-link ke service
   - Cek variables `${{MySQL.*}}`
   - Entrypoint script akan wait max 60 seconds

3. **Cek APP_KEY:**
   - Entrypoint script akan generate otomatis
   - Jika masih error, set manual di Railway

4. **Cek Migrations:**
   - Error akan terlihat jelas di logs
   - Cek error message untuk detail

### Database Connection Failed

**Error:** `SQLSTATE[HY000] [2002] Connection refused`

**Solution:**
- Pastikan database sudah di-link ke service
- Cek variables `${{MySQL.*}}`
- Entrypoint script akan wait max 60 seconds untuk database connection

### Migrations Failed

**Error:** `SQLSTATE[42S02]: Base table or view not found`

**Solution:**
- Cek error message di logs
- Pastikan database connection berhasil
- Entrypoint script akan menampilkan error dengan jelas

### Seeders Failed

**Error:** Seeder error

**Solution:**
- Cek error message di logs
- Entrypoint script akan continue meskipun seeders gagal
- Set `RUN_SEEDERS=false` untuk skip seeders

## 📝 Manual Commands (Railway Shell)

Jika perlu run commands manual:

```bash
# Generate APP_KEY
php artisan key:generate --force

# Run migrations
php artisan migrate --force

# Run seeders
php artisan db:seed --force

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🎯 Tips

1. **Set APP_DEBUG=true** sementara untuk debugging
2. **Cek logs** dengan Railway Deploy Logs
3. **Entrypoint script** akan menampilkan semua error dengan jelas
4. **Wait time** untuk database connection adalah 60 seconds
5. **Migrations dan seeders** akan menampilkan error jika gagal
6. **Set RUN_SEEDERS=false** jika tidak ingin run seeders otomatis

## ✅ Checklist

- [ ] Code sudah di-push ke repository
- [ ] Repository sudah di-connect ke Railway
- [ ] MySQL database sudah di-create dan di-link
- [ ] Environment variables sudah di-set
- [ ] Database variables menggunakan `${{MySQL.*}}`
- [ ] RUN_SEEDERS sudah di-set (true untuk run seeders, false untuk skip)
- [ ] APP_DEBUG sudah di-set (false untuk production)
- [ ] APP_URL sesuai dengan Railway domain

---

**Happy Deploying! 🚀**

