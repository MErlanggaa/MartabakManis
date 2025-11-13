# 🔧 Fix Gateway Timeout Error

## Masalah
Error "Gateway Timeout" terjadi karena:
1. Query database terlalu lama
2. HTTP request ke external API timeout
3. Execution time melebihi batas

## ✅ Solusi yang Sudah Diterapkan

### 1. Database Timeout Configuration
- Menambahkan `timeout` dan `PDO::ATTR_TIMEOUT` di config database
- Query akan timeout setelah 10 detik

### 2. HTTP Request Optimization
- OpenStreetMap API: timeout 5 detik dengan retry 2x
- Google Maps API: sudah ada timeout handling

### 3. Query Optimization
- Eager loading sudah digunakan (`with()`)
- Pagination sudah diterapkan

## 🚀 Quick Fix

### Opsi 1: Increase PHP Timeout (Temporary)

Edit file `.env` atau `php.ini`:
```ini
max_execution_time=60
```

Atau di controller, tambahkan di awal method:
```php
set_time_limit(60); // 60 seconds
```

### Opsi 2: Optimize Query (Permanent)

Jika timeout terjadi di halaman katalog:

1. **Kurangi pagination:**
   ```php
   $layanan = $query->paginate(8); // Kurangi dari 12 ke 8
   ```

2. **Disable filter jarak jika lambat:**
   - Hapus filter jarak dari query jika tidak diperlukan
   - Atau buat filter jarak sebagai optional

3. **Add Index di Database:**
   ```sql
   CREATE INDEX idx_umkm_lat_lng ON umkm(latitude, longitude);
   CREATE INDEX idx_layanan_nama ON layanan(nama);
   ```

### Opsi 3: Cache Results

Tambahkan caching untuk query yang sering dipanggil:

```php
$layanan = Cache::remember("katalog_{$cacheKey}", 300, function() use ($query) {
    return $query->paginate(12);
});
```

## 🔍 Debugging

### Cek Query yang Lambat

1. **Enable Query Log:**
   ```php
   DB::enableQueryLog();
   // ... your code ...
   dd(DB::getQueryLog());
   ```

2. **Cek Execution Time:**
   ```php
   $start = microtime(true);
   // ... your code ...
   $time = microtime(true) - $start;
   \Log::info("Execution time: {$time} seconds");
   ```

### Cek Halaman yang Timeout

- **Katalog:** Mungkin query dengan filter jarak
- **Detail UMKM:** Mungkin HTTP request ke geocoding API
- **Detail Layanan:** Mungkin eager loading terlalu banyak

## 📝 Rekomendasi

1. **Untuk Development:**
   - Increase timeout ke 60 detik
   - Monitor query log

2. **Untuk Production:**
   - Optimize query dengan index
   - Implement caching
   - Reduce pagination size
   - Consider using queue untuk heavy operations

## ⚠️ Jika Masih Timeout

1. **Cek Database Connection:**
   ```bash
   php artisan tinker
   DB::connection()->getPdo();
   ```

2. **Cek External API:**
   - Test OpenStreetMap API langsung
   - Test Google Maps API jika digunakan

3. **Cek Server Resources:**
   - Memory usage
   - CPU usage
   - Database connection pool

## 🎯 Quick Test

Jalankan ini untuk test apakah masalahnya di query atau API:

```bash
# Test database connection
php artisan tinker
>>> \App\Models\Layanan::count();

# Test HTTP request
php artisan tinker
>>> \Illuminate\Support\Facades\Http::timeout(5)->get('https://nominatim.openstreetmap.org/reverse?format=json&lat=-6.2&lon=106.8');
```


