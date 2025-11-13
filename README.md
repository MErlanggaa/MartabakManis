# UMKM.go - Platform Katalog UMKM Indonesia

Website katalog UMKM dengan fitur-fitur canggih untuk membantu UMKM berkembang dan memudahkan user menemukan UMKM terdekat dengan berbagai layanan dan rekomendasi AI.

## 🚀 Fitur Utama

### 🏢 Admin
- **Dashboard** dengan statistik UMKM lengkap
- **Kelola akun UMKM** (tambah, edit, hapus, upload PDF)
- **Integrasi Maps** untuk konversi alamat ke koordinat (OpenStreetMap default, Google Maps opsional)
- **Monitoring data UMKM** dan laporan bug dari user
- **Sistem laporan** - kelola dan respon laporan dari user/UMKM
- **Upload PDF** ke sistem AI untuk training model

### 🏪 UMKM (Pemilik UMKM)
- **Dashboard** dengan grafik keuntungan interaktif
- **Kelola profil UMKM** (nama, deskripsi, foto, lokasi, nomor WA)
- **Input data keuntungan** manual atau upload Excel
- **Visualisasi data keuntungan** dengan Chart.js (harian, mingguan, bulanan)
- **Kelola layanan** yang ditawarkan (tambah, edit, hapus)
- **AI Konsultasi Bisnis** - dapatkan saran strategis untuk mengembangkan UMKM
- **Sistem komentar** - lihat komentar dari user untuk UMKM dan layanan
- **History laporan** - lihat status laporan yang dikirim
- **Statistik views** - pantau berapa kali UMKM dilihat
- **Dashboard statistik** - Total Dilihat, Total Layanan, Total Favorit

### 👤 User
- **Katalog UMKM** dengan hero section menarik
- **Pencarian UMKM** berdasarkan nama, layanan, atau produk
- **Filter canggih** berdasarkan favorit, jarak, dan rekomendasi
- **Sistem favorit** UMKM
- **AI Rekomendasi** berdasarkan metadata dan preferensi user
- **AI Chat Assistant** - tanyakan tentang produk, layanan, atau rekomendasi
- **Integrasi Leaflet maps** untuk menampilkan lokasi
- **Detail UMKM** dengan peta interaktif dan informasi lengkap
- **Detail layanan** dengan komentar dan rating terpisah
- **Sistem komentar & rating** untuk UMKM dan layanan
- **History laporan** - lihat status laporan yang dikirim
- **Share & Copy Link** untuk membagikan UMKM/layanan

### 🔧 Fitur Teknis
- **Loading Screen** dengan logo dan animasi menarik untuk transisi halaman
- **SweetAlert2** untuk semua notifikasi dan konfirmasi
- **View Counter** untuk UMKM dan layanan
- **Responsive Design** - bekerja sempurna di desktop dan mobile
- **Mobile Access** - dapat diakses dari HP melalui jaringan lokal
- **Error Handling** - halaman 404 Not Found custom
- **Optimasi performa** - gateway timeout fix, database timeout configuration

## 🛠 Teknologi yang Digunakan

### Backend
- **Laravel 11** - PHP Framework
- **MySQL** - Database
- **Laravel Auth** - Authentication dengan role-based access control
- **Maatwebsite/Excel** - Import/Export Excel

### Frontend
- **Tailwind CSS** - Utility-first CSS framework
- **Vite** - Build tool dan HMR
- **Leaflet Maps** - Peta interaktif
- **Chart.js** - Grafik dan visualisasi data
- **SweetAlert2** - Notifikasi yang menarik
- **Font Awesome** - Icons
- **JavaScript (ES6+)** - Vanilla JS untuk interaktivitas

### API & Integrasi
- **OpenStreetMap Nominatim API** - Geocoding dan reverse geocoding (Default, gratis, tidak perlu API key)
- **Google Maps Geocoding API** - Fallback opsional untuk konversi alamat (perlu API key)
- **AI Service** (Railway) - `ai-martabakmanis-production.up.railway.app`
  - AI Chat untuk user
  - AI Consultation untuk UMKM
  - PDF upload untuk training

## 📊 Struktur Database

### Tabel Users
- `id`, `name`, `email`, `password`, `role` (admin/umkm/user), `favorites`, `created_at`, `updated_at`

### Tabel UMKM
- `id`, `user_id`, `nama`, `description`, `latitude`, `longitude`, `photo_path`, `favorit_count`, `jenis_umkm`, `no_wa`, `views`, `created_at`, `updated_at`

### Tabel Layanan
- `id`, `nama`, `price`, `description`, `photo_path`, `created_at`, `updated_at`

### Tabel Layanan UMKM (Pivot)
- `id`, `layanan_id`, `umkm_id`, `created_at`, `updated_at`

### Tabel Keuntungan
- `id`, `umkm_id`, `periode_type` (harian/mingguan/bulanan), `tanggal`, `minggu`, `bulan`, `pendapatan`, `pengeluaran`, `keuntungan_bersih`, `jumlah_transaksi`, `created_at`, `updated_at`

### Tabel Favorites
- `id`, `user_id`, `umkm_id`, `created_at`, `updated_at`

### Tabel Comments
- `id`, `user_id`, `umkm_id`, `layanan_id`, `comment`, `rating`, `created_at`, `updated_at`

### Tabel Reports
- `id`, `user_id`, `nama`, `email`, `kategori`, `judul`, `deskripsi`, `status`, `respon_admin`, `admin_id`, `created_at`, `updated_at`

## 📦 Instalasi

### Opsi 1: Menggunakan Docker (Recommended) 🐳

**Mudah dan cepat, tidak perlu install MySQL atau PHP secara manual!**

Lihat dokumentasi lengkap di [DOCKER.md](DOCKER.md)

**Quick Start:**
```bash
# Clone repository
git clone <repository-url>
cd umkm-app

# Setup environment
cp .env.example .env

# Build dan start containers
docker-compose up -d --build

# Setup aplikasi
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed

# Build assets (Production)
docker-compose exec node npm install
docker-compose exec node npm run build

# Atau run dev server (Development)
docker-compose exec node npm run dev
```

Akses aplikasi di: **http://localhost:8000**

### Opsi 2: Instalasi Manual

### 1. Clone Repository
```bash
git clone <repository-url>
cd umkm-app
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Setup Environment
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Konfigurasi Database
Edit file `.env` dan sesuaikan konfigurasi database:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=umkm
DB_USERNAME=root
DB_PASSWORD=

# Database timeout configuration
DB_TIMEOUT=10
```

### 5. Setup Google Maps API (Optional)
**Catatan:** Aplikasi sudah menggunakan **OpenStreetMap Nominatim API** secara default (gratis, tidak perlu API key). Google Maps API hanya opsional untuk fallback jika ingin menggunakan Google Maps.

**Jika ingin menggunakan Google Maps (Opsional):**
1. Daftar di [Google Cloud Console](https://console.cloud.google.com/)
2. Aktifkan **Google Maps Geocoding API**
3. Tambahkan API key ke `.env`:
```env
GOOGLE_MAPS_API_KEY=your_api_key_here
```

**Jika tidak menggunakan Google Maps API:**
- Aplikasi akan otomatis menggunakan OpenStreetMap Nominatim API
- Tidak perlu konfigurasi tambahan
- Gratis dan tidak perlu API key

### 6. Build Assets
```bash
# Development mode
npm run dev

# Production mode
npm run build
```

### 7. Jalankan Migration dan Seeder
```bash
php artisan migrate
php artisan db:seed
```

### 8. Jalankan Aplikasi
```bash
# Development server
php artisan serve

# Atau dengan Vite (untuk hot reload)
npm run dev
```

Aplikasi akan berjalan di `http://localhost:8000`

## 📱 Akses dari Mobile Device

### Persiapan
1. Pastikan HP dan komputer terhubung ke WiFi yang sama
2. Tutup firewall Windows (atau izinkan port 8000)

### Cara Mengakses

**Opsi 1: Script Otomatis (Recommended)**
1. Build assets terlebih dahulu: `npm run build`
2. Double-click file `start-mobile.bat`
3. Akses dari HP: `http://<IP-COMPUTER>:8000`

**Opsi 2: Manual**
```bash
# Terminal 1 - Laravel Server
php artisan serve --host=0.0.0.0 --port=8000

# Terminal 2 - Vite Dev Server (jika perlu hot reload)
npm run dev
```

**Cek IP Address:**
```bash
ipconfig
# Cari "IPv4 Address" di bagian WiFi adapter Anda
```

### Troubleshooting Mobile Access
- Pastikan firewall tidak memblokir port 8000
- Pastikan HP dan komputer di WiFi yang sama
- Gunakan IP address dari WiFi adapter, bukan Ethernet
- Untuk production, gunakan web server seperti Nginx/Apache

## 👥 Akun Default

Setelah menjalankan seeder, Anda dapat login dengan akun berikut:

- **Admin**: `admin@umkm.com` / `password`
- **UMKM**: `umkm@example.com` / `password`
- **User**: `user@example.com` / `password`

## ✨ Fitur Detail

### AI Rekomendasi
Sistem AI rekomendasi menggunakan metadata dari:
- Jenis UMKM favorit user
- Layanan yang sering difavoritkan
- Range harga yang disukai
- Lokasi geografis

### AI Chat Assistant
- Chat interaktif untuk user
- Tanya tentang produk, layanan, atau rekomendasi
- Quick actions untuk pertanyaan umum
- Response real-time dari AI service

### AI Konsultasi Bisnis
- Konsultasi khusus untuk pemilik UMKM
- Saran strategis berdasarkan data UMKM
- Tips bisnis otomatis
- Konteks berdasarkan profil UMKM dan data keuangan

### Sistem Komentar & Rating
- Komentar terpisah untuk UMKM dan layanan
- Rating 1-5 bintang
- Edit dan hapus komentar sendiri
- UMKM bisa lihat semua komentar di dashboard

### Sistem Laporan
- User dan UMKM bisa mengirim laporan bug
- Admin bisa kelola dan respon laporan
- Status tracking (pending, diproses, selesai)
- History laporan per user

### Upload Excel
Format Excel untuk data keuntungan:
| bulan | pendapatan | pengeluaran | jumlah_transaksi |
|-------|------------|-------------|------------------|
| Januari 2025 | 5000000 | 3000000 | 150 |

**Template Excel** dapat diunduh dari dashboard UMKM.

## 🔌 API Endpoints

### Maps & Geocoding
- **OpenStreetMap Nominatim** (Default) - Konversi alamat ke koordinat dan sebaliknya
- **Google Maps API** (Opsional) - Fallback jika Google Maps API key tersedia

### User Endpoints
- `GET /user/katalog` - Katalog UMKM
- `GET /user/umkm/{id}` - Detail UMKM
- `GET /user/layanan/{id}` - Detail layanan
- `GET /user/recommendations` - AI rekomendasi UMKM
- `POST /user/favorite/{id}/toggle` - Toggle favorit UMKM
- `GET /user/distance/{umkmId}/{lat}/{lng}` - Hitung jarak
- `GET /user/ai-chat` - Halaman AI Chat
- `POST /user/ai-chat/send` - Kirim pesan ke AI
- `GET /user/history-laporan` - History laporan user
- `POST /user/layanan/{id}/comment` - Tambah komentar layanan
- `PUT /user/layanan/comment/{id}` - Edit komentar layanan
- `DELETE /user/layanan/comment/{id}` - Hapus komentar layanan

### UMKM Endpoints
- `GET /umkm/dashboard` - Dashboard UMKM
- `POST /umkm/profile/update` - Update profil
- `POST /umkm/keuntungan/store` - Tambah data keuntungan
- `GET /umkm/keuntungan/{id}` - Get data keuntungan
- `PUT /umkm/keuntungan/{id}/update` - Update data keuntungan
- `DELETE /umkm/keuntungan/{id}/delete` - Hapus data keuntungan
- `POST /umkm/excel/upload` - Upload Excel keuntungan
- `GET /umkm/excel/template` - Download template Excel
- `POST /umkm/layanan/update` - Update layanan
- `POST /umkm/layanan/{id}/update` - Update layanan (edit mode)
- `DELETE /umkm/layanan/{id}/remove` - Hapus layanan
- `GET /umkm/ai-consultation` - Halaman AI Konsultasi
- `POST /umkm/ai-consultation/chat` - Kirim pesan konsultasi
- `GET /umkm/ai-consultation/tips` - Get tips bisnis
- `GET /umkm/komentar` - Lihat semua komentar
- `GET /umkm/history-laporan` - History laporan UMKM

### Admin Endpoints
- `GET /admin/dashboard` - Dashboard Admin
- `GET /admin/umkm` - List UMKM
- `POST /admin/umkm/create` - Tambah UMKM
- `GET /admin/umkm/{id}/edit` - Edit UMKM
- `PUT /admin/umkm/{id}/update` - Update UMKM
- `DELETE /admin/umkm/{id}/delete` - Hapus UMKM
- `POST /admin/upload-pdf` - Upload PDF ke AI
- `GET /admin/laporan` - List laporan
- `GET /admin/laporan/{id}/detail` - Detail laporan
- `PUT /admin/laporan/{id}/status` - Update status laporan
- `DELETE /admin/laporan/{id}` - Hapus laporan

### Public Endpoints
- `GET /katalog` - Katalog UMKM (public)
- `GET /laporan` - Halaman laporan bug
- `POST /laporan` - Submit laporan bug

## 🎨 UI/UX Features

- **Hero Section** dengan split design dan search bar terintegrasi
- **Loading Screen** dengan logo dan animasi untuk transisi halaman
- **SweetAlert2** untuk semua notifikasi dan konfirmasi
- **Responsive Design** - mobile-first approach
- **Interactive Maps** - Leaflet dengan marker dan geocoding
- **Real-time Updates** - tanpa reload halaman untuk beberapa fitur
- **Toast Notifications** - feedback instan untuk aksi user

## 🐛 Troubleshooting

### Gateway Timeout Error
- Database timeout sudah dikonfigurasi di `config/database.php`
- HTTP client timeout untuk external API sudah ditingkatkan
- PHP execution time limit sudah ditambahkan

### Mobile Access Loading
- Pastikan build assets sudah dijalankan (`npm run build`)
- Gunakan script `start-mobile.bat` untuk akses mobile
- Pastikan Vite dev server tidak berjalan jika menggunakan built assets

### Modal tidak muncul
- Periksa console browser untuk error JavaScript
- Pastikan SweetAlert2 sudah di-load
- Pastikan tidak ada konflik CSS

## 📝 Catatan Pengembangan

### Konfigurasi Vite untuk Mobile Access
File `vite.config.js` dapat dikonfigurasi untuk akses mobile:
```javascript
server: {
    host: '0.0.0.0',
    port: 5173,
    hmr: {
        host: 'YOUR_IP_ADDRESS'
    }
}
```

### AI Service Integration
AI service endpoint: `https://ai-martabakmanis-production.up.railway.app`
- `/chat` - Endpoint untuk AI chat
- `/admin/upload` - Endpoint untuk upload PDF

## 🤝 Kontribusi

1. Fork repository
2. Buat feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

## 📄 Lisensi

Distributed under the MIT License. See `LICENSE` for more information.

## 📞 Kontak

- Email: your-email@example.com
- Project Link: [https://github.com/your-username/umkm-app](https://github.com/your-username/umkm-app)

---

**Dibuat dengan ❤️ untuk UMKM Indonesia**
