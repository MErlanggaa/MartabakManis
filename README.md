# UMKM Katalog

Website katalog UMKM dengan fitur-fitur canggih untuk membantu UMKM berkembang dan memudahkan user menemukan UMKM terdekat.

## Fitur Utama

### 🏢 Admin
- Dashboard dengan statistik UMKM
- Kelola akun UMKM (tambah, edit, hapus)
- Integrasi Google Maps untuk konversi alamat ke koordinat
- Monitoring data UMKM

### 🏪 UMKM (Pemilik UMKM)
- Dashboard dengan grafik keuntungan
- Kelola profil UMKM (nama, deskripsi, foto, lokasi)
- Input data keuntungan manual atau upload Excel
- Visualisasi data keuntungan dengan Chart.js
- Kelola layanan yang ditawarkan

### 👤 User
- Katalog UMKM dengan fitur pencarian
- Filter berdasarkan favorit dan jarak
- Sistem favorit UMKM
- AI rekomendasi berdasarkan metadata
- Integrasi Leaflet maps untuk lokasi

## Teknologi yang Digunakan

- **Backend**: Laravel 11
- **Frontend**: Bootstrap 5, Leaflet Maps, Chart.js
- **Database**: MySQL
- **Maps**: Google Maps API, Leaflet
- **Excel**: Maatwebsite/Excel
- **Authentication**: Laravel Auth dengan role-based access

## Struktur Database

### Tabel Users
- `id`, `name`, `email`, `password`, `role`, `favorites`, `created_at`, `updated_at`

### Tabel UMKM
- `id`, `user_id`, `nama`, `description`, `favorite`, `latitude`, `longitude`, `photo_path`, `favorit_count`, `jenis_umkm`, `created_at`, `updated_at`

### Tabel Layanan
- `id`, `nama`, `price`, `photo_path`, `created_at`, `updated_at`

### Tabel Layanan UMKM (Pivot)
- `id`, `layanan_id`, `umkm_id`, `created_at`, `updated_at`

### Tabel Keuntungan
- `id`, `umkm_id`, `bulan`, `pendapatan`, `pengeluaran`, `keuntungan_bersih`, `jumlah_transaksi`, `created_at`, `updated_at`

## Instalasi

1. **Clone repository**
   ```bash
   git clone <repository-url>
   cd umkm-app
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Setup environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Konfigurasi database**
   Edit file `.env` dan sesuaikan konfigurasi database:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=umkm
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Setup Google Maps API**
   - Daftar di [Google Cloud Console](https://console.cloud.google.com/)
   - Aktifkan Google Maps Geocoding API
   - Tambahkan API key ke `.env`:
   ```
   GOOGLE_MAPS_API_KEY=your_api_key_here
   ```

6. **Jalankan migration dan seeder**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

7. **Jalankan aplikasi**
   ```bash
   php artisan serve
   ```

## Akun Default

Setelah menjalankan seeder, Anda dapat login dengan akun berikut:

- **Admin**: admin@umkm.com / password
- **UMKM**: umkm@example.com / password  
- **User**: user@example.com / password

## Fitur AI Rekomendasi

Sistem AI rekomendasi menggunakan metadata dari:
- Jenis UMKM favorit user
- Layanan yang sering difavoritkan
- Range harga yang disukai
- Lokasi geografis

## Upload Excel

Format Excel untuk data keuntungan:
| bulan | pendapatan | pengeluaran | jumlah_transaksi |
|-------|------------|-------------|------------------|
| Januari 2025 | 5000000 | 3000000 | 150 |

## API Endpoints

### Google Maps
- `POST /api/geocode` - Konversi alamat ke koordinat
- `POST /api/reverse-geocode` - Konversi koordinat ke alamat
- `GET /api/nearby-places` - Cari tempat terdekat

### User
- `GET /user/recommendations` - AI rekomendasi UMKM
- `POST /user/favorite/{id}/toggle` - Toggle favorit UMKM

## Kontribusi

1. Fork repository
2. Buat feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

## Lisensi

Distributed under the MIT License. See `LICENSE` for more information.

## Kontak

- Email: your-email@example.com
- Project Link: [https://github.com/your-username/umkm-app](https://github.com/your-username/umkm-app)