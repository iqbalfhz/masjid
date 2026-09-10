# Tutorial Instalasi Lokal (Development)
# Sistem Informasi Masjid An-Nur

**Versi:** 2.0
**Tanggal:** 10 September 2026
**Untuk:** Environment development di komputer lokal

> Dokumen ini sudah disesuaikan dengan kondisi nyata project setelah setup selesai
> (versi paket, nama perintah, dan langkah yang benar-benar dijalankan).

---

## 1. Prasyarat (Requirements)

| Software | Versi Minimum | Terpasang saat setup | Cek dengan |
|---|---|---|---|
| PHP | 8.3+ | 8.4.20 | `php -v` |
| Composer | 2.x | 2.8.5 | `composer -V` |
| Node.js | 20+ | 22.12.0 | `node -v` |
| NPM | 10+ | 11.4.1 | `npm -v` |
| MySQL | 8.0+ (atau MariaDB 10.6+) | 8.4.6 | `mysql --version` |
| Git | Terbaru | — | `git --version` |

**Ekstensi PHP yang wajib aktif:**
`openssl`, `pdo_mysql`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd`

> `openssl` dipakai bukan hanya untuk HTTPS, tapi juga untuk membuat kunci VAPID
> reminder sholat (langkah 8).

---

## 2. Clone Repository

```bash
git clone <url-repository> masjid-annur
cd masjid-annur
```

---

## 3. Install Dependency

```bash
composer install
npm install
```

Paket utama yang ikut terpasang:

| Paket | Versi | Fungsi |
|---|---|---|
| `laravel/framework` | 13.31 | Framework aplikasi |
| `filament/filament` | 5.8 | Admin panel |
| `bezhansalleh/filament-shield` | 4.3 | Role & permission (di atas `spatie/laravel-permission`) |
| `spatie/laravel-activitylog` | 5.1 | Log aktivitas / audit trail |
| `spatie/laravel-tags` | 4.12 | Tag lintas modul |
| `saade/filament-fullcalendar` | 4.0-beta | Kalender kegiatan di admin panel |
| `barryvdh/laravel-dompdf` | 3.1 | Export PDF laporan keuangan |
| `laravel/scout` | 11.7 | Pencarian global lintas modul |

---

## 4. Konfigurasi Environment (.env)

```bash
cp .env.example .env
php artisan key:generate
```

Bagian yang perlu disesuaikan:

```env
APP_NAME="Masjid An-Nur"
APP_ENV=local
APP_URL=http://localhost:8000
APP_LOCALE=id

# WAJIB: aplikasi memakai jam dinding masjid, bukan UTC.
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=masjid_annur
DB_USERNAME=root
DB_PASSWORD=

# Upload galeri/e-library butuh disk publik
FILESYSTEM_DISK=public

# Jadwal sholat (Aladhan API), koordinat default area Tangcity Mall
PRAYER_API_PROVIDER=aladhan
PRAYER_API_LATITUDE=-6.178306
PRAYER_API_LONGITUDE=106.631417
PRAYER_API_METHOD=20

# Pencarian global — driver database cukup untuk skala masjid
SCOUT_DRIVER=database
SCOUT_QUEUE=false

# Web Push (diisi di langkah 8)
VAPID_PUBLIC_KEY=
VAPID_PRIVATE_KEY=
VAPID_SUBJECT=mailto:admin@masjidannur.test
```

> **Zona waktu itu penting.** Jadwal sholat dari API disimpan sebagai jam lokal
> (WIB), sedangkan perhitungan "waktu sholat berikutnya" memakai `now()`. Bila
> `APP_TIMEZONE` dibiarkan `UTC`, keduanya meleset 7 jam dan beranda akan
> menampilkan waktu sholat yang sudah lewat. Untuk masjid di zona lain, ganti
> ke `Asia/Makassar` (WITA) atau `Asia/Jayapura` (WIT).

> `PRAYER_API_METHOD=20` adalah metode hisab Kementerian Agama RI. Koordinat dan
> metode ini juga bisa diubah lewat menu **Pengaturan Umum** di admin panel tanpa
> menyentuh `.env`.

---

## 5. Buat Database

```sql
CREATE DATABASE masjid_annur CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

## 6. Jalankan Migration & Seeder

```bash
php artisan migrate --seed
```

Seeder yang dijalankan:

| Seeder | Isi |
|---|---|
| `RoleSeeder` | 5 role + seluruh permission sesuai matriks PRD 5.3 |
| `UserSeeder` | 5 akun pengurus (PRD 8.1) |
| `MasterDataSeeder` | Pengaturan masjid, kategori keuangan & artikel, fasilitas, FAQ |
| `DemoContentSeeder` | Konten contoh — **hanya jalan di environment local/testing** |

Akun yang tersedia setelah seeding:

| Role | Email | Password (lokal) |
|---|---|---|
| Superadmin | superadmin@masjidannur.test | password |
| Admin (Pengurus Masjid) | admin@masjidannur.test | password |
| Ketua DKM | ketua@masjidannur.test | password |
| Sekretaris | sekretaris@masjidannur.test | password |
| Bendahara | bendahara@masjidannur.test | password |

> Password `password` **hanya** dipakai saat `APP_ENV=local` atau `testing`. Di
> environment lain `UserSeeder` membuat password acak dan menampilkannya sekali di
> output terminal — catat saat itu juga.

---

## 7. Filament & Permission

Panel admin sudah terkonfigurasi di repository, jadi `filament:install` **tidak
perlu diulang**. Yang perlu diketahui:

- Permission memakai format Shield `{aksi}:{model}` dengan `snake_case`
  (contoh: `view_any:announcement`, `approve:article`), diatur di
  `config/filament-shield.php`.
- `RoleSeeder` adalah sumber kebenaran pemetaan role → permission. Setelah
  menambah Resource baru, jalankan:

```bash
php artisan shield:generate --all --panel=admin --option=policies_and_permissions
php artisan db:seed --class=RoleSeeder
```

- Bila permission terasa tidak berlaku setelah diubah:

```bash
php artisan permission:cache-reset
```

> Superadmin tidak bergantung pada daftar permission — ia lolos seluruh pengecekan
> lewat `Gate::before` di `AppServiceProvider`.

---

## 8. Setup Web Push Notification (Opsional)

```bash
php artisan masjid:vapid-keys
```

Salin dua baris hasilnya ke `.env`, lalu aktifkan reminder lewat
**Pengaturan Umum → Jadwal Sholat & Reminder** di admin panel.

> Fitur ini memakai implementasi Web Push bawaan aplikasi (`App\Services\WebPushService`),
> bukan paket `minishlink/web-push`, karena paket tersebut belum mendukung Guzzle 8
> yang dipakai Laravel 13.

---

## 9. Storage & Build Asset

```bash
# Agar file upload (galeri, poster, QRIS) bisa diakses publik
php artisan storage:link

npm run build
# atau mode development dengan hot-reload:
npm run dev
```

---

## 10. Ambil Jadwal Sholat Pertama Kali

```bash
php artisan masjid:sync-prayer-schedules
```

Perintah ini mengambil jadwal bulan berjalan + 2 bulan berikutnya dari Aladhan API.
Jadwal yang ditandai **override** oleh pengurus tidak akan tertimpa.

---

## 11. Jalankan Server Lokal

```bash
php artisan serve
```

- **Website publik:** http://localhost:8000
- **Admin panel:** http://localhost:8000/admin

Alternatif satu perintah (server + queue + vite berbarengan):

```bash
composer run dev
```

---

## 12. Scheduler & Queue

Dua tugas terjadwal terdaftar di `routes/console.php`:

| Perintah | Jadwal | Fungsi |
|---|---|---|
| `masjid:sync-prayer-schedules` | Harian 01:30 | Perbarui jadwal sholat dari API |
| `masjid:send-prayer-reminders` | Tiap menit | Kirim reminder push ke jamaah |

Saat development, jalankan di terminal terpisah:

```bash
php artisan schedule:work
php artisan queue:listen
```

---

## 13. Menjalankan Test

```bash
php artisan test --compact
```

Test memakai SQLite in-memory (lihat `phpunit.xml`), jadi tidak menyentuh database
MySQL development.

---

## 14. Troubleshooting Umum

| Masalah | Penyebab & Solusi |
|---|---|
| `SQLSTATE[HY000] [1045] Access denied` | Cek `DB_USERNAME`/`DB_PASSWORD` di `.env` |
| Halaman blank / error 500 | `php artisan config:clear && php artisan cache:clear`, cek `storage/logs/laravel.log` |
| Tampilan berantakan | Jalankan `npm run build`, pastikan `public/build` terisi |
| Gambar upload tidak muncul | Jalankan `php artisan storage:link` dan pastikan `FILESYSTEM_DISK=public` |
| `Class not found` setelah install paket | `composer dump-autoload` |
| Menu admin tidak muncul untuk suatu role | `php artisan db:seed --class=RoleSeeder` lalu `php artisan permission:cache-reset` |
| `masjid:vapid-keys` gagal | PHP tidak menemukan `openssl.cnf`; perintah sudah menyediakan config minimal otomatis di `storage/app/` — pastikan folder tersebut bisa ditulis |
| Waktu sholat berikutnya meleset beberapa jam | `APP_TIMEZONE` belum diisi. Set `APP_TIMEZONE=Asia/Jakarta` di `.env`, lalu `php artisan config:clear` |
| Sinkronisasi jadwal sholat gagal | Cek koneksi internet. Jadwal lama tetap dipakai; admin bisa input manual di menu Jadwal Sholat |

---

## 15. Langkah Selanjutnya

1. Login sebagai Superadmin, cek menu sudah sesuai matriks role (PRD 5.3).
2. Isi **Pengaturan Umum**: identitas masjid, rekening donasi, QRIS, koordinat.
3. Ganti akun dummy dengan data pengurus asli sebelum go-live.
4. Hapus konten contoh bila tidak diperlukan (`DemoContentSeeder` tidak jalan di production).
