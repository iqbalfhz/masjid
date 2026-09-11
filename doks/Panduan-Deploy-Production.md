# Panduan Deploy Production
# Sistem Informasi Masjid An-Nur

Dokumen ini memenuhi PRD bagian 13 poin 8 (*dokumentasi deploy sebagai deliverable
wajib*). Ditujukan untuk orang yang memasang sistem ini ke server — bukan untuk
Tim DKM yang memakainya sehari-hari.

Kodebase ini dirancang untuk **dipasang ulang per masjid**, bukan multi-tenant
(PRD bagian 13). Jadi satu masjid = satu instalasi + satu database. Bagian 11
menjelaskan cara memasangnya untuk masjid kedua.

---

## 1. Kebutuhan Server

| Komponen | Minimal | Catatan |
|---|---|---|
| PHP | 8.3 (diuji di 8.4) | `composer.json` menuntut `^8.3` |
| MySQL | 8.0 | MariaDB 10.6+ juga jalan |
| Web server | **FrankenPHP** | Berbasis Caddy, PHP tertanam. Menggantikan Nginx + PHP-FPM + Certbot sekaligus |
| Composer | 2.x | |
| Node.js | 20+ | Hanya untuk build aset; tidak perlu ada saat runtime |
| Disk | ~1 GB + ruang upload | Galeri dan e-library tumbuh seiring waktu |
| RAM | 1 GB | VPS kecil cukup (PRD bagian 6) |

> **PHP muncul dua kali, dan itu disengaja.** FrankenPHP membawa PHP-nya sendiri
> untuk melayani request. Tapi Composer, Artisan, dan scheduler cron berjalan di
> baris perintah — dan itu butuh PHP CLI. Anda punya dua pilihan:
>
> 1. Pasang PHP CLI sistem juga (`php8.3-cli` beserta ekstensinya), lalu pakai
>    `php artisan ...` seperti biasa; atau
> 2. Tidak memasang PHP sistem sama sekali, dan memakai PHP bawaan FrankenPHP
>    lewat `frankenphp php-cli artisan ...` di **setiap** perintah — termasuk di
>    dalam cron.
>
> Panduan ini memakai `php artisan` (pilihan 1). Bila Anda memilih 2, ganti
> setiap `php artisan` menjadi `frankenphp php-cli artisan`. Yang berbahaya
> adalah mencampur keduanya: versi dan daftar ekstensi PHP sistem bisa berbeda
> dari milik FrankenPHP, sehingga perintah berhasil di terminal tapi gagal saat
> melayani request — atau sebaliknya.

**Ekstensi PHP** (diambil dari `require` dependency yang benar-benar terpasang,
bukan daftar umum):

`ctype`, `curl`, `dom`, `fileinfo`, `filter`, `hash`, `iconv`, `intl`, `json`,
`libxml`, `mbstring`, `openssl`, `pcre`, `pdo`, `pdo_mysql`, `session`,
`tokenizer`, `xmlreader`, `zip`.

Tiga yang paling sering belum aktif di hosting, padahal wajib:

| Ekstensi | Dibutuhkan oleh | Kalau tidak ada |
|---|---|---|
| `intl` | `filament/support` | Admin panel gagal dimuat sama sekali |
| `zip` + `xmlreader` | `openspout` | Export rekap format XLSX gagal |
| `openssl` | Laravel | `masjid:vapid-keys` gagal, Web Push tidak bisa disiapkan |

> `gd` **tidak** diperlukan: tidak ada dependency yang menuntutnya, dan aplikasi
> ini tidak melakukan pengolahan gambar di sisi server — berkas unggahan
> disimpan apa adanya.

Cek cepat di server:

```bash
php -v
php -m | tr '\n' ' '
mysql --version
composer --version
```

Verifikasi ekstensi wajib sekaligus:

```bash
php -r 'foreach (["ctype","curl","dom","fileinfo","filter","hash","iconv","intl","json","libxml","mbstring","openssl","pcre","pdo_mysql","session","tokenizer","xmlreader","zip"] as $e) { printf("%-12s %s\n", $e, extension_loaded($e) ? "ok" : "HILANG"); }'
```

---

## 2. Menyiapkan Berkas

```bash
cd /var/www
git clone <url-repository> masjid
cd masjid

composer install --no-dev --optimize-autoloader
```

> `--no-dev` penting: paket development (Pest, Faker) tidak perlu ada di server
> dan hanya memperbesar permukaan yang harus diamankan.

Build aset. Boleh dilakukan di mesin lokal lalu kirim folder `public/build`,
kalau server tidak punya Node:

```bash
npm ci
npm run build
```

---

## 3. Konfigurasi `.env`

```bash
cp .env.example .env
php artisan key:generate
```

Isi seperti berikut:

```env
APP_NAME="Masjid An-Nur"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://masjidannur.or.id
APP_LOCALE=id
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=masjid_annur
DB_USERNAME=masjid
DB_PASSWORD=<password-kuat>

FILESYSTEM_DISK=public

PRAYER_API_PROVIDER=aladhan
PRAYER_API_LATITUDE=-6.178306
PRAYER_API_LONGITUDE=106.631417
PRAYER_API_METHOD=20

SCOUT_DRIVER=database
SCOUT_QUEUE=false

VAPID_PUBLIC_KEY=
VAPID_PRIVATE_KEY=
VAPID_SUBJECT=mailto:admin@masjidannur.or.id

LOG_CHANNEL=daily
LOG_LEVEL=warning

# Di belakang reverse proxy atau Cloudflare Tunnel (lihat "Header keamanan dan CSP")
TRUSTED_PROXIES=*

# Biarkan kosong. Sakelar darurat CSP — lihat "Header keamanan dan CSP"
# CSP_REPORT_ONLY=true
```

### Tiga nilai yang paling sering salah

**`APP_DEBUG=false`.** Bila `true`, halaman error menampilkan isi `.env` —
termasuk password database — kepada siapa pun yang memicunya. Ini kebocoran
kredensial, bukan sekadar tampilan berantakan.

**`APP_URL` harus alamat produksi lengkap dengan `https://`.** Perintah yang
berjalan lewat CLI (scheduler, seeder) tidak punya request untuk dijadikan
acuan, sehingga membangun tautan dari `APP_URL`. Salah isi berarti tautan di
pengingat Web Push mengarah ke alamat yang keliru.

**`APP_TIMEZONE` harus zona masjid.** Jadwal sholat dari API disimpan sebagai jam
lokal, sedangkan "waktu sholat berikutnya" dihitung dari `now()`. Bila dibiarkan
`UTC`, keduanya meleset 7 jam dan beranda menampilkan waktu sholat yang sudah
lewat. Untuk masjid di zona lain: `Asia/Makassar` (WITA) atau `Asia/Jayapura`
(WIT) — singkatan zonanya ikut menyesuaikan otomatis di tampilan.

### Header keamanan dan CSP

Header keamanan (HSTS, anti-clickjacking, Content-Security-Policy, dan lainnya)
dipasang oleh aplikasi sendiri, bukan oleh web server, jadi tidak ada yang perlu
dikonfigurasi di Caddy. Daftar lengkap dan alasannya ada di PRD bagian 6.2.

**`TRUSTED_PROXIES`.** Di belakang Cloudflare Tunnel atau reverse proxy, request
sampai ke aplikasi sebagai HTTP biasa. Tanpa mempercayai proxy, aplikasi mengira
dirinya tidak diakses lewat HTTPS: header HSTS tidak dikirim dan tautan dibangun
dengan `http://`. Nilai `*` mempercayai proxy mana pun — aman **hanya bila port
aplikasi tidak bisa dijangkau langsung dari luar**, misalnya port container yang
terikat ke `127.0.0.1` (`127.0.0.1:8087:80`). Bila port terbuka ke internet,
isi dengan IP proxy yang sebenarnya.

**`CSP_REPORT_ONLY` — sakelar darurat.** Halaman publik memblokir semua skrip
inline. Bila setelah update ada fitur publik yang mendadak mati — tombol tidak
bereaksi, dan konsol browser menampilkan `violates the following Content
Security Policy` — isi `CSP_REPORT_ONLY=true` lalu muat ulang konfigurasi
(`php artisan config:cache`; di Coolify cukup ubah variabel lalu **Restart**).
Kebijakan tetap dikirim tapi tidak lagi memblokir. Setelah kodenya diperbaiki
(skrip dipindah ke `resources/js/app.js`), hapus lagi variabel ini.

Setelah deploy, periksa hasilnya di <https://securityheaders.com>.

---

## 4. Database & Data Awal

```bash
php artisan migrate --force
php artisan db:seed --force
```

> `--force` diperlukan karena Laravel menolak migrasi dan seeding di
> `production` tanpa konfirmasi.

`db:seed` aman dijalankan di server: `DatabaseSeeder` hanya memanggil
`RoleSeeder`, `UserSeeder`, dan `MasterDataSeeder`, sedangkan `DemoContentSeeder`
dipagari `app()->environment(['local', 'testing'])` sehingga tidak akan ikut
jalan selama `APP_ENV=production`.

Jangan pernah memanggil `php artisan db:seed --class=DemoContentSeeder` secara
manual di server — pemanggilan langsung melewati pagar itu dan akan mengisi
database dengan konten contoh.

`UserSeeder` membuat akun dummy tiap peran. **Ganti password semuanya sebelum
go-live**, atau buat akun pengurus asli lalu hapus yang dummy.

Daftarkan permission Filament Shield:

```bash
php artisan shield:generate --all --panel=admin
php artisan permission:cache-reset
```

---

## 5. Storage, Izin Berkas, dan Cache

```bash
php artisan storage:link
```

Web server harus bisa menulis ke dua folder ini, dan **hanya** dua folder ini:

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

> Jangan `chmod -R 777` ke seluruh proyek. Itu mengizinkan siapa pun yang punya
> pijakan di server menulis ulang kode aplikasi Anda.

Optimalkan untuk production:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan icons:cache
```

> Setelah `config:cache`, perubahan `.env` **tidak lagi terbaca** sampai Anda
> menjalankan `php artisan config:clear`. Ini penyebab paling umum "sudah saya
> ubah tapi tidak ngefek".

---

## 6. Web Server — FrankenPHP

Sistem ini dilayani **FrankenPHP**, server aplikasi PHP berbasis Caddy. PHP
tertanam di dalam prosesnya, jadi **tidak ada PHP-FPM, tidak ada Nginx, dan
tidak ada Certbot** — ketiganya digantikan satu binary.

Document root tetap **wajib** menunjuk ke `public/`. Kalau salah, `.env` bisa
diunduh lewat browser.

### Pasang

```bash
curl https://frankenphp.dev/install.sh | sh
sudo mv frankenphp /usr/local/bin/
frankenphp version
```

Binary-nya sudah membawa PHP sendiri. Verifikasi ekstensi wajib memakai PHP
milik FrankenPHP, **bukan** PHP sistem:

```bash
frankenphp php-cli -r 'foreach (["intl","zip","xmlreader","openssl","pdo_mysql"] as $e) { printf("%-12s %s\n", $e, extension_loaded($e) ? "ok" : "HILANG"); }'
```

> Ini langkah yang mudah terlewat. `php -m` di server bisa menunjukkan semuanya
> lengkap, sementara PHP yang benar-benar melayani request adalah milik
> FrankenPHP — dan isinya bisa berbeda.

### Caddyfile

Simpan di `/etc/frankenphp/Caddyfile`:

```caddy
masjidannur.or.id {
    root * /var/www/masjid/public
    encode zstd br gzip

    # Batas unggahan: e-library PDF & video galeri
    request_body {
        max_size 20MB
    }

    # Tolak akses ke berkas tersembunyi, kecuali ACME
    @hidden path_regexp /\.(?!well-known)
    respond @hidden 403

    php_server
}
```

`request_body max_size` harus selaras dengan `upload_max_filesize` dan
`post_max_size` di `php.ini` FrankenPHP — kalau tidak, unggahan besar gagal
tanpa pesan yang jelas.

### Jalankan sebagai service

```ini
# /etc/systemd/system/frankenphp.service
[Unit]
Description=FrankenPHP - Masjid An-Nur
After=network.target mysql.service

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/var/www/masjid
ExecStart=/usr/local/bin/frankenphp run --config /etc/frankenphp/Caddyfile
ExecReload=/bin/kill -USR1 $MAINPID
Restart=always
RestartSec=5

# Caddy butuh port 80/443 tanpa harus berjalan sebagai root
AmbientCapabilities=CAP_NET_BIND_SERVICE

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now frankenphp
sudo systemctl status frankenphp
```

### HTTPS otomatis

Caddy mengurus sertifikat Let's Encrypt sendiri — terbit dan diperpanjang
otomatis, **tanpa Certbot dan tanpa cron pembaruan sertifikat**. Syaratnya:

1. Domain di Caddyfile sudah mengarah ke IP server (A/AAAA record).
2. Port **80 dan 443** terbuka. Port 80 tidak boleh ditutup — dipakai untuk
   tantangan ACME.
3. Alamat di Caddyfile adalah nama domain, bukan `:80`.

HTTPS bukan sekadar praktik baik di sini: **Web Push tidak akan bekerja
tanpanya**, karena browser menolak Service Worker di koneksi tidak aman. Jadi
pengingat sholat bergantung pada langkah ini.

Untuk uji coba lokal tanpa domain, ganti baris pertama Caddyfile menjadi
`http://localhost` — Caddy akan melewati HTTPS. Ingat bahwa Web Push tidak bisa
diuji dalam kondisi itu.

### Worker mode — jangan diaktifkan dulu

FrankenPHP bisa menahan aplikasi di memori antar-request (lewat Laravel Octane),
yang membuatnya jauh lebih cepat. Untuk skala satu masjid, keuntungannya kecil
sedangkan risikonya nyata — jadi **mode klasik sudah memadai** dan itulah yang
dipakai konfigurasi di atas.

Bila suatu saat ingin mengaktifkannya, pahami perubahan aturannya lebih dulu:
aplikasi tidak lagi dibangun ulang tiap request, sehingga apa pun yang disimpan
di properti statis atau binding `singleton` akan **bocor antar-pengunjung**.

Proyek ini sudah dipersiapkan untuk itu di satu titik yang diketahui:
`PublicLayoutComposer` memoize identitas masjid, pengumuman berjalan, dan
penanda menu aktif. Penanda itu berasal dari request yang sedang berjalan, jadi
bindingnya sengaja `scoped`, bukan `singleton` — lihat `AppServiceProvider`.
Dengan `singleton`, menu akan tersorot mengikuti halaman yang dibuka pengunjung
**pertama** dan pengaturan masjid membeku sampai worker di-restart.
`tests/Feature/WorkerModeTest.php` menjaga perilaku ini.

Langkahnya bila tetap ingin diaktifkan:

```bash
composer require laravel/octane
php artisan octane:install --server=frankenphp
php artisan test          # wajib hijau sebelum lanjut
```

Lalu jalankan lewat `php artisan octane:start --server=frankenphp` di service
systemd, dan **tambahkan `php artisan octane:reload` ke prosedur update** —
tanpa itu, kode baru tidak akan aktif karena worker masih memegang versi lama.

---

## 7. Cron — Bagian yang Paling Mudah Terlupa

Dua pekerjaan menopang masjid di latar belakang. Keduanya bergantung pada satu
baris cron:

```bash
sudo crontab -e -u www-data
```

```cron
* * * * * cd /var/www/masjid && php artisan schedule:run >> /dev/null 2>&1
```

> **Cron tidak ikut disediakan FrankenPHP.** Server aplikasi hanya melayani
> request HTTP; pekerjaan terjadwal tetap butuh cron sistem. Ini mudah terlewat
> justru karena FrankenPHP menggantikan begitu banyak komponen lain.
>
> Bila server tidak memasang PHP CLI sistem (lihat bagian 1), barisnya menjadi:
>
> ```cron
> * * * * * cd /var/www/masjid && /usr/local/bin/frankenphp php-cli artisan schedule:run >> /dev/null 2>&1
> ```
>
> Pakai jalur absolut ke binary-nya — `PATH` milik cron biasanya jauh lebih
> sempit daripada di terminal Anda.

| Perintah | Jadwal | Kalau tidak jalan |
|---|---|---|
| `masjid:sync-prayer-schedules` | Harian 01:30 | Jadwal sholat di website publik jadi basi dan akhirnya habis |
| `masjid:send-prayer-reminders` | Tiap menit | Pengingat berhenti terkirim ke jamaah |

> **Kegagalannya tidak bersuara.** Bila cron mati, tidak ada pesan error di mana
> pun. Yang pertama menyadari adalah jamaah yang salah datang waktu subuh.
> Karena itu dashboard punya kartu **Kesehatan Sistem** — lihat bagian 9.

Ambil jadwal sholat pertama kali, jangan menunggu 01:30:

```bash
php artisan masjid:sync-prayer-schedules
```

### Queue worker tidak diperlukan

Ini keputusan sadar. Tidak ada pekerjaan berat yang perlu ditunda di sistem skala
satu masjid, sementara bergantung pada worker berarti fitur diam-diam berhenti
setiap kali worker mati:

- **Notifikasi internal** dikirim langsung lewat
  `App\Support\ImmediateDatabaseNotification`. Menyimpannya hanya satu INSERT.
- **Export rekap** dijalankan `sync` supaya berkas jadi saat itu juga.
- **Reminder Web Push** sudah berjalan di proses cron-nya sendiri.

Jadi **tidak perlu** menyiapkan Supervisor. Kalau nanti ada pekerjaan berat baru
(misalnya email massal), barulah siapkan worker — dan ingat bahwa kegagalannya
senyap.

---

## 8. Web Push (Pengingat Sholat)

```bash
php artisan masjid:vapid-keys
```

Salin kedua kunci ke `.env`, lalu `php artisan config:clear && php artisan config:cache`.

> **Kunci VAPID hanya dibuat sekali.** Menggantinya membuat seluruh langganan
> jamaah yang sudah terdaftar menjadi tidak sah, dan mereka harus berlangganan
> ulang satu per satu. Simpan cadangannya bersama backup `.env`.

Aktifkan lewat admin panel: **Pengaturan Umum → Pengingat Sholat**.

---

## 9. Verifikasi Setelah Deploy

Jalankan berurutan. Ini daftar yang benar-benar sering gagal, bukan formalitas.

| # | Periksa | Cara | Lolos bila |
|---|---|---|---|
| 1 | Halaman publik hidup | Buka `https://domain` | Beranda tampil, waktu sholat **sesuai jam dinding** |
| 2 | `.env` tidak terekspos | Buka `https://domain/.env` | 404 atau 403 |
| 3 | Debug mati | Picu URL ngawur, mis. `/xyz` | Halaman 404 biasa, bukan jejak error Laravel |
| 4 | Aset terbangun | Lihat tampilan | Tata letak rapi, bukan HTML polos |
| 5 | Login admin | `https://domain/admin` | Bisa masuk |
| 6 | Upload berfungsi | Unggah 1 foto galeri | Gambar tampil setelah disimpan |
| 7 | **Cron hidup** | Dashboard → Kesehatan Sistem | "Jadwal sholat tersedia" berangka wajar, bukan "Habis" |
| 8 | Notifikasi sampai | Kirim 1 testimoni dari form publik | Lonceng sekretaris bertambah |
| 9 | Tautan notifikasi benar | Klik "Lihat detail" | Masuk ke halaman yang sesuai |
| 10 | Peran sesuai matriks | Login tiap peran | Menu & dashboard sesuai PRD 5.3 / 5.4 |
| 11 | Export jalan | Keuangan → Export | Berkas terunduh |
| 12 | HTTPS & Push | Izinkan notifikasi di HP | Langganan tersimpan |
| 13 | Service tahan restart | `sudo reboot`, tunggu, buka situs | Situs hidup sendiri tanpa dijalankan manual |
| 14 | Sertifikat terbit benar | Klik gembok di browser | Diterbitkan Let's Encrypt, bukan sertifikat internal Caddy |

Poin 7 adalah cara termurah memastikan cron benar-benar hidup. Kembali cek
keesokan harinya: bila angkanya tidak bertambah, `schedule:run` tidak jalan.

Poin 13 memastikan `systemctl enable` benar-benar dijalankan. Tanpa itu semuanya
tampak normal sampai server pertama kali reboot, lalu situs mati dan tidak ada
yang tahu penyebabnya.

Poin 14 membedakan dua kondisi yang mirip di layar: Caddy menerbitkan sertifikat
internal bila gagal menjangkau Let's Encrypt. Situs tetap terbuka lewat HTTPS,
tapi browser pengunjung akan memperingatkannya — dan Web Push tetap gagal.

---

## 10. Update Versi

```bash
cd /var/www/masjid
php artisan down --render="errors::503"

git pull origin main
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan icons:cache

sudo systemctl reload frankenphp
php artisan up
```

> `reload` memuat ulang konfigurasi tanpa memutus koneksi yang sedang berjalan.
> Di mode klasik sebenarnya tidak wajib — tiap request sudah memuat kode
> terbaru — tapi memasukkannya ke prosedur sejak awal menghindari satu kelas
> kebingungan nanti: **kalau worker mode diaktifkan, kode baru tidak akan aktif
> tanpa langkah ini**, dan gejalanya membingungkan karena berkasnya jelas sudah
> berubah. Bila memakai Octane, ganti dengan `php artisan octane:reload`.

**Backup dulu sebelum migrasi**, selalu:

```bash
mysqldump -u masjid -p masjid_annur > ~/backup-$(date +%F).sql
tar -czf ~/storage-$(date +%F).tar.gz storage/app/public
```

Dua hal itu yang tidak bisa dibuat ulang: isi database dan berkas unggahan.
Kodenya selalu bisa di-clone lagi.

Otomatiskan harian:

```cron
0 2 * * * mysqldump -u masjid -p'<password>' masjid_annur | gzip > /backup/db-$(date +\%F).sql.gz
```

> Backup yang belum pernah dicoba dipulihkan belum tentu backup. Uji restore
> ke database kosong sekali waktu.

---

## 11. Memasang untuk Masjid Lain

Kodebase ini dirancang ulang-pakai (PRD bagian 13). Untuk masjid kedua:

1. Clone ke direktori terpisah, buat database terpisah.
2. Sesuaikan `.env`: `APP_NAME`, `APP_URL`, kredensial DB, dan **`APP_TIMEZONE`**
   sesuai zona masjid tersebut.
3. Sesuaikan `PRAYER_API_LATITUDE` / `PRAYER_API_LONGITUDE` ke koordinat masjid,
   atau ubah belakangan lewat **Pengaturan Umum**.
4. Buat kunci VAPID **baru** — jangan pakai ulang milik masjid lain.
5. Jalankan `php artisan migrate --force` lalu `php artisan db:seed --force`.
6. Isi identitas, rekening donasi, QRIS, dan struktur pengurus lewat admin panel.

Tidak ada nilai khusus Masjid An-Nur yang tertanam di dalam kode — semuanya
lewat `.env` atau tabel pengaturan. Zona waktu, koordinat, metode hisab, dan
identitas masjid semuanya bisa diubah tanpa menyentuh kode.

---

## 12. Troubleshooting Production

| Masalah | Penyebab & Solusi |
|---|---|
| Error 500 di semua halaman | Cek `storage/logs/laravel.log`. Paling sering: izin `storage/` atau `APP_KEY` kosong |
| Perubahan `.env` tidak berpengaruh | `php artisan config:clear` lalu `config:cache` |
| Tampilan polos tanpa gaya | `public/build` belum ada. Jalankan `npm run build` |
| Gambar upload 404 | `php artisan storage:link` belum dijalankan, atau symlink hilang setelah deploy ulang |
| Jadwal sholat berhenti / "Habis" | Cron tidak jalan. Cek `crontab -l -u www-data` dan uji `php artisan schedule:run` manual |
| Waktu sholat meleset berjam-jam | `APP_TIMEZONE` salah atau config masih ter-cache |
| Push notification tidak terkirim | Butuh HTTPS. Cek kunci VAPID terisi dan pengingat aktif di Pengaturan Umum |
| Lonceng notifikasi kosong | Cek `DB::table('jobs')->count()`. Bila menumpuk, ada kode yang mengantrekan notifikasi — seharusnya dikirim langsung |
| Tautan notifikasi salah alamat | `APP_URL` tidak sesuai domain produksi |
| Menu admin tidak lengkap | `php artisan shield:generate --all --panel=admin` lalu `permission:cache-reset` |
| Upload besar gagal diam-diam | Selaraskan `request_body max_size` (Caddyfile) dengan `upload_max_filesize` & `post_max_size` (php.ini FrankenPHP) |
| Situs tidak bisa diakses setelah deploy | `sudo systemctl status frankenphp` dan `sudo journalctl -u frankenphp -n 50` |
| Sertifikat HTTPS gagal terbit | Port 80 tertutup (dipakai tantangan ACME) atau DNS belum mengarah ke server. Cek `journalctl -u frankenphp | grep -i acme` |
| Kode baru tidak aktif setelah update | Bila memakai worker mode, jalankan `php artisan octane:reload`. Di mode klasik, `sudo systemctl reload frankenphp` |
| Ekstensi PHP ada di `php -m` tapi error saat request | PHP sistem dan PHP bawaan FrankenPHP berbeda. Cek dengan `frankenphp php-cli -m` |
| Fitur di halaman publik tidak bereaksi; konsol browser berisi `violates the following Content Security Policy` | Ada skrip atau atribut `on…=` inline yang diblokir CSP publik. Sementara: `CSP_REPORT_ONLY=true` (bagian 3). Permanen: pindahkan perilakunya ke `resources/js/app.js` |
| Peta di halaman kontak kosong | Tautan harus berupa embed Google Maps. Domain peta lain diblokir `frame-src` — tambahkan di `SecurityHeaders::PUBLIK` bila memang perlu |
| securityheaders.com tidak menemukan HSTS | Aplikasi tidak tahu dirinya diakses lewat HTTPS. Pastikan `TRUSTED_PROXIES` terisi, lalu cache ulang config |

---

## 13. Ringkasan Keamanan

- [ ] `APP_DEBUG=false` dan `APP_ENV=production`
- [ ] `root` di Caddyfile menunjuk ke `public/`, bukan folder proyek
- [ ] HTTPS aktif dengan sertifikat Let's Encrypt, bukan sertifikat internal Caddy
- [ ] FrankenPHP berjalan sebagai `www-data`, bukan `root`
- [ ] `systemctl enable frankenphp` sudah dijalankan (tahan reboot)
- [ ] Password akun dummy sudah diganti atau akunnya dihapus
- [ ] Password database kuat dan berbeda dari akun lain
- [ ] `storage/` dan `bootstrap/cache/` 775, sisanya tidak ditulis web server
- [ ] `DemoContentSeeder` tidak pernah dijalankan di server
- [ ] Backup harian berjalan dan **pernah diuji restore**
- [ ] `composer install` memakai `--no-dev`
- [ ] Header keamanan terkirim — periksa di securityheaders.com (sebelum audit nilainya F)
- [ ] `CSP_REPORT_ONLY` tidak diisi; `true` hanya untuk keadaan darurat
- [ ] Bila `TRUSTED_PROXIES=*`, port aplikasi hanya terikat ke `127.0.0.1`

Rate limiting form publik (testimoni, saran, RSVP, pendaftaran) sudah aktif dari
kode sesuai PRD bagian 6 — tidak perlu konfigurasi tambahan di server.
