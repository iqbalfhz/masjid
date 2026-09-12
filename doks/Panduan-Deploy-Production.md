# Panduan Deploy Production
# Sistem Informasi Masjid An-Nur

Dokumen ini memenuhi PRD bagian 13 poin 8 (*dokumentasi deploy sebagai deliverable
wajib*). Ditujukan untuk orang yang memasang sistem ini ke server — bukan untuk
Tim DKM yang memakainya sehari-hari.

Panduan ini mengikuti instalasi yang benar-benar berjalan di
`https://masjid.iqbalfhz.my.id`: **Coolify** di server sendiri, aplikasi dibangun
dari `Dockerfile` di repo, dan diakses publik lewat **Cloudflare Tunnel**.

```
Pengunjung ──HTTPS──▶ Cloudflare ──tunnel──▶ cloudflared (di server, network host)
                                                   │ HTTP
                                                   ▼
                                 127.0.0.1:8087 ──▶ container aplikasi :80 (FrankenPHP)
                                                        ├──▶ MySQL (resource Coolify, jaringan internal)
                                                        └──▶ volume masjid-storage (berkas unggahan)
```

Kodebase ini dirancang untuk **dipasang ulang per masjid**, bukan multi-tenant
(PRD bagian 13). Satu masjid = satu aplikasi + satu database. Bagian 11
menjelaskan cara memasangnya untuk masjid kedua.

---

## 1. Gambaran Arsitektur

| Komponen | Wujudnya | Catatan |
|---|---|---|
| Server | VM Ubuntu di Proxmox, menjalankan Coolify | |
| Aplikasi | Satu container dari `Dockerfile` repo | FrankenPHP + PHP 8.4. Build pack **Dockerfile**, bukan Nixpacks |
| Database | Resource MySQL terpisah di Coolify | Terhubung lewat jaringan internal Docker, tidak dibuka ke luar |
| Berkas unggahan | Volume `masjid-storage` | Satu-satunya data aplikasi yang harus bertahan melewati redeploy |
| HTTPS & domain | Cloudflare Tunnel | Tidak ada port server yang dibuka ke internet |
| Pekerjaan terjadwal | Scheduled Task Coolify | Pengganti cron |
| Deploy | Webhook GitHub → Coolify | Setiap push ke `main` langsung menjadi deploy produksi |

Yang **sengaja tidak ada**, beserta alasannya:

- **Redis.** Cache dan session memakai database. Untuk skala satu masjid,
  menambah Redis berarti satu layanan lagi yang bisa mati tanpa keuntungan
  berarti.
- **Queue worker.** Tidak ada pekerjaan yang diantrekan — lihat bagian 6.
- **docker-compose.** Satu container ditambah resource MySQL Coolify sudah
  cukup. Compose menambah satu jebakan nyata: nama volume di berkas compose
  harus persis sama dengan volume yang sudah ada, kalau tidak Coolify membuat
  volume **baru yang kosong** dan seluruh unggahan tampak hilang.
- **Nginx, PHP-FPM, Certbot.** FrankenPHP melayani PHP langsung, sedangkan TLS
  diselesaikan Cloudflare.

---

## 2. Isi Image

Semua persiapan sudah tertulis di `Dockerfile`, `docker/Caddyfile`, dan
`docker/entrypoint.sh`. Bagian ini menjelaskan isinya supaya tidak ada yang
dikerjakan ulang secara manual.

### Tahap build

| Tahap | Isi |
|---|---|
| `aset` | `node:22-alpine` — `npm ci` lalu `npm run build`. Node tidak ikut ke image akhir |
| `basis` | `dunglas/frankenphp:php8.4-bookworm` + ekstensi `intl`, `zip`, `pdo_mysql`, `opcache` + Composer |
| `vendor` | `composer install --no-dev` memakai PHP yang sama dengan runtime |
| runtime | Kode aplikasi, `vendor/`, `public/build`, Caddyfile, entrypoint |

Dua keputusan di sini lahir dari build yang pernah gagal:

- **Composer dijalankan di atas image FrankenPHP, bukan image `composer:2`.**
  PHP di image Composer tidak punya `ext-intl`, padahal `filament/support`
  mensyaratkannya, sehingga `composer install` menolak jalan.
- **Tidak ada yang diunduh saat build selain paket npm dan Composer.** Font
  Instrument Sans disimpan di `resources/fonts/`. Plugin font online sempat
  menggagalkan build dengan `EAI_AGAIN`, karena lingkungan build tidak selalu
  bisa menjangkau layanan font.

### Setelan runtime

| Setelan | Nilai | Alasan |
|---|---|---|
| `SERVER_NAME` | `:80` | HTTPS bawaan FrankenPHP dimatikan; TLS urusan Cloudflare |
| `opcache.validate_timestamps` | `0` | Kode tidak pernah berubah di dalam container — **jangan menyunting berkas lewat Terminal**, perubahan tidak akan terbaca |
| `upload_max_filesize` / `post_max_size` | `20M` | Batas unggahan e-library dan galeri |
| `memory_limit` | `256M` | |
| `HEALTHCHECK` | `curl -fsS http://127.0.0.1:80/up` | `/up` menjawab tanpa menyentuh database, jadi menguji "aplikasi melayani permintaan" |

**Ekstensi PHP** yang benar-benar dituntut dependency: `intl` (Filament — tanpa
itu admin panel tidak dimuat), `zip` dan `xmlreader` (export XLSX), `openssl`
(kunci VAPID), `pdo_mysql`. Selain `intl`, `zip`, `pdo_mysql`, dan `opcache`,
semuanya sudah bawaan image FrankenPHP. `gd` **tidak** diperlukan — berkas
unggahan disimpan apa adanya.

### Caddyfile

`php_server` dibiarkan polos, tanpa subdirektif. Dua hal yang pernah
menjatuhkan container:

- Caddy memakai regex **RE2** milik Go, yang tidak mendukung *lookahead*. Pola
  gaya Nginx seperti `/\.(?!well-known)` membuat Caddy menolak konfigurasinya
  dan container restart terus-menerus.
- `php_server` tidak menerima `try_files`.

`.env` tetap aman tanpa aturan penolak berkas titik: letaknya di `/app`,
sedangkan yang dilayani hanya `/app/public`.

### Entrypoint — dijalankan setiap container start

| Langkah | Kenapa di runtime, bukan saat build |
|---|---|
| Buat folder `storage/` dan atur izin | Volume mulanya kosong |
| `storage:link --force` | Container baru tidak membawa symlink `public/storage` |
| `filament:assets` | CSS/JS Filament di-gitignore dan lahir di tahap build yang dibuang. Tanpa langkah ini admin panel tampil sebagai HTML polos |
| `migrate --force` | Skema selalu mengikuti kode. Aman diulang |
| `config:cache`, `route:cache`, `view:cache`, `icons:cache` | Environment baru lengkap setelah Coolify menyuntikkannya |

Akibatnya: **tidak ada langkah manual di setiap deploy**, dan **perubahan
environment baru berlaku setelah Restart** — `config:cache` membekukan nilainya
saat container start.

### Worker mode — jangan diaktifkan dulu

FrankenPHP bisa menahan aplikasi di memori antar-request (lewat Laravel
Octane). Untuk skala satu masjid keuntungannya kecil sedangkan risikonya nyata,
jadi image ini memakai **mode klasik**.

Bila suatu saat ingin mengaktifkannya: aplikasi tidak lagi dibangun ulang tiap
request, sehingga apa pun yang disimpan di properti statis atau binding
`singleton` bocor antar-pengunjung. Satu titik yang sudah dipersiapkan:
`PublicLayoutComposer` sengaja di-bind `scoped`, bukan `singleton` (lihat
`AppServiceProvider`), dan `tests/Feature/WorkerModeTest.php` menjaganya.
Pasang `laravel/octane`, pastikan seluruh test hijau, lalu ubah `CMD` di
`Dockerfile`.

---

## 3. Database — MySQL di Coolify

**+ New → Database → MySQL**, lalu isi:

| Field | Nilai |
|---|---|
| Normal User / Normal User Password | Tetapkan **sebelum Start pertama** |
| Initial Database | `masjid_annur` |
| Custom MySQL Configuration | lihat di bawah |
| Ports Mappings | **kosong** |
| Make it publicly available | **tidak dicentang** |

```ini
[mysqld]
character-set-server=utf8mb4
collation-server=utf8mb4_unicode_ci
```

Collation disamakan dengan bawaan Laravel. Bila berbeda, suatu hari muncul
galat `Illegal mix of collations` yang sulit dilacak.

**Save → Start.** Setelah berjalan, ambil host dari **MySQL URL (internal)** —
berupa ID acak seperti `c9rlue88ncdzpslhco01kctd`. Itulah `DB_HOST`: bukan
`localhost`, bukan IP server.

> Coolify memperingatkan: *"If you change the values in the database, please
> sync it here"*. Artinya bila kredensial diubah langsung di dalam MySQL,
> nilainya harus disamakan di halaman ini — kalau tidak, backup otomatis
> Coolify gagal. Paling aman: ubah kredensial lewat Coolify, sebelum Start
> pertama.

---

## 4. Aplikasi di Coolify

### 4.1 Buat resource

**+ New → Application**, pilih repository (Public Repository, atau lewat GitHub
App bila repo privat), branch `main`, build pack **Dockerfile**. Base Directory
`/` dan lokasi Dockerfile `/Dockerfile` biarkan bawaan.

### 4.2 Configuration → General

| Field | Nilai | Catatan |
|---|---|---|
| Domains | `https://masjid.iqbalfhz.my.id` | Lengkap dengan `https://` |
| Ports Exposes | **`80`** | Coolify sering mengisi `3000`. Salah isi = container hidup, tapi tidak ada yang menjawab |
| Port Mappings | **`127.0.0.1:8087:80`** | Pintu masuk tunnel, hanya terjangkau dari server sendiri |

Lalu lintas publik tidak melewati proxy Coolify: tunnel menyambung langsung ke
port 8087 (bagian 5). Nomor 8087 dipilih mengikuti urutan aplikasi lain di
server ini.

**Ikatan ke `127.0.0.1` hanya bekerja bila `cloudflared` berjalan dengan network
mode `host`.** Periksa di server:

```bash
docker inspect $(docker ps -q --filter "name=cloudflared") --format '{{.HostConfig.NetworkMode}}'
```

Harus `host`. Bila hasilnya `bridge` atau nama jaringan lain, ikatan loopback
membuat tunnel tidak bisa menjangkau aplikasi — pakai `8087:80` dan tutup port
itu di firewall server.

### 4.3 Persistent Storage

**+ Add → Volume Mount:**

| Field | Nilai |
|---|---|
| Name | `masjid-storage` |
| Destination Path | `/app/storage/app/public` |

**Hanya path itu.** Volume ini untuk data yang tidak bisa dibuat ulang — berkas
unggahan. Sisa `storage/` (cache, view terkompilasi) memang dibuang di setiap
deploy.

Docker menamai volumenya `<uuid-aplikasi>-masjid-storage`, dengan isi di
`/var/lib/docker/volumes/<uuid-aplikasi>-masjid-storage/_data` — jalur ini yang
dipakai untuk backup (bagian 10). Pastikan benar-benar terpasang:

```bash
docker inspect $(docker ps -q --filter "name=<uuid-aplikasi>" | head -1) --format '{{json .Mounts}}'
```

Harus memuat `"Destination":"/app/storage/app/public"`. Bila hasilnya `[]`,
volume tidak terpasang dan **semua unggahan hilang di redeploy berikutnya**. Ini
pernah terjadi: sampul album, QRIS, dan logo yang diunggah sebelum volume
dipasang tidak bisa dipulihkan dan harus diunggah ulang.

### 4.4 Environment Variables

Isi di **Environment Variables**. Tidak ada variabel yang dibutuhkan saat build —
semuanya dibaca saat container start.

```env
APP_NAME="Masjid An-Nur"
APP_ENV=production
APP_KEY=base64:...                      # php artisan key:generate --show (di lokal)
APP_DEBUG=false
APP_URL=https://masjid.iqbalfhz.my.id
APP_LOCALE=id
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=<host dari MySQL URL (internal)>
DB_PORT=3306
DB_DATABASE=masjid_annur
DB_USERNAME=<Normal User>
DB_PASSWORD=<Normal User Password>

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public
LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=local

PRAYER_API_PROVIDER=aladhan
PRAYER_API_LATITUDE=-6.178306
PRAYER_API_LONGITUDE=106.631417
PRAYER_API_METHOD=20

SCOUT_DRIVER=database
SCOUT_QUEUE=false

VAPID_PUBLIC_KEY=                       # bagian 8
VAPID_PRIVATE_KEY=
VAPID_SUBJECT=mailto:<email pengelola>

LOG_LEVEL=warning

TRUSTED_PROXIES=*
# CSP_REPORT_ONLY=true                  # jangan diisi — sakelar darurat
```

Tanda `<…>` hanya penanda tempat: ganti seluruhnya, **termasuk tanda kurung
sudutnya**. Contoh: `VAPID_SUBJECT=mailto:nama@domain.com`, bukan
`VAPID_SUBJECT=mailto:<nama@domain.com>`.

Setiap perubahan: **Save, lalu Restart.**

### Nilai yang paling sering salah

**`APP_KEY`.** Buat sekali di lokal dengan `php artisan key:generate --show`,
tempel lengkap dengan awalan `base64:`, dan simpan salinannya di luar server.
Kosong berarti aplikasi menolak jalan; berganti berarti semua sesi login putus
dan data terenkripsi tidak terbaca lagi.

**`APP_DEBUG=false`.** Bila `true`, halaman error menampilkan isi environment —
termasuk password database — kepada siapa pun yang memicunya.

**`APP_URL` harus alamat produksi lengkap dengan `https://`.** Perintah yang
berjalan lewat CLI (scheduler, seeder) tidak punya request untuk dijadikan
acuan, sehingga membangun tautan dari `APP_URL`. Salah isi berarti tautan di
notifikasi dan pengingat Web Push mengarah ke alamat yang keliru.

**`APP_TIMEZONE` harus zona masjid.** Jadwal sholat dari API disimpan sebagai jam
lokal, sedangkan "waktu sholat berikutnya" dihitung dari `now()`. Bila dibiarkan
`UTC`, keduanya meleset 7 jam. Untuk masjid di zona lain: `Asia/Makassar` (WITA)
atau `Asia/Jayapura` (WIT).

**`SESSION_SECURE_COOKIE=true`.** Cookie login hanya dikirim lewat HTTPS. Aman
karena seluruh lalu lintas publik datang lewat Cloudflare.

**`LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=local`.** Berkas yang sedang diunggah
disimpan sementara sebelum disimpan permanen. Bawaannya mengikuti disk default
— yaitu `public` — sehingga berkas itu sempat bisa diakses siapa pun lewat
`/storage/livewire-tmp/…`, dan ikut terbawa ke setiap arsip backup. Pratinjau
unggahan tetap bekerja: Livewire menyajikannya lewat rute bertanda tangan.

Koordinat dan metode hisab juga bisa diubah belakangan lewat **Pengaturan Umum**
di admin panel, tanpa menyentuh environment.

### Header keamanan dan CSP

Header keamanan (HSTS, anti-clickjacking, Content-Security-Policy, dan lainnya)
dipasang oleh aplikasi sendiri, bukan oleh web server. Daftar lengkap dan
alasannya ada di PRD bagian 6.2. Hasil pemindaian securityheaders.com saat ini:
**A+** (sebelum audit: F).

**`TRUSTED_PROXIES`.** Request dari tunnel sampai ke aplikasi sebagai HTTP biasa.
Tanpa mempercayai proxy, aplikasi mengira dirinya tidak diakses lewat HTTPS:
header HSTS tidak dikirim dan tautan dibangun dengan `http://`. Nilai `*`
mempercayai proxy mana pun — aman **hanya karena port aplikasi terikat ke
`127.0.0.1`** (bagian 4.2). Bila port dibuka ke jaringan lain, isi dengan IP
proxy yang sebenarnya.

**`CSP_REPORT_ONLY` — sakelar darurat.** Halaman publik memblokir semua skrip
inline. Bila setelah update ada fitur publik yang mendadak mati — tombol tidak
bereaksi, dan konsol browser menampilkan `violates the following Content
Security Policy` — isi `CSP_REPORT_ONLY=true`, Save, lalu **Restart**.
Kebijakan tetap dikirim tapi tidak lagi memblokir. Setelah kodenya diperbaiki
(skrip dipindah ke `resources/js/app.js`), hapus lagi variabel ini.

### 4.5 Deploy pertama

Klik **Deploy**. Build memakan beberapa menit (`npm ci` dan `composer install`);
ikuti lognya di **Deployments**. Bila Healthcheck di Coolify diaktifkan, isi path
`/up` dan port `80` — image sudah membawa `HEALTHCHECK` sendiri dengan tujuan
yang sama.

Setelah container berjalan, buka tab **Terminal** aplikasi dan jalankan
**sekali saja**:

```bash
php artisan db:seed --force
php artisan shield:generate --all --panel=admin
php artisan permission:cache-reset
php artisan masjid:sync-prayer-schedules
```

> **`db:seed` mencetak password akun pengurus satu kali saja — catat saat itu
> juga.** Di produksi password dibuat acak dan tidak ditampilkan lagi. Ini
> pernah terjadi: password tidak tercatat, tidak ada yang bisa login, dan
> password harus direset lewat Terminal (bagian 12).

`db:seed` aman di produksi: `DatabaseSeeder` hanya memanggil `RoleSeeder`,
`UserSeeder`, dan `MasterDataSeeder`. `DemoContentSeeder` dipagari
`app()->environment(['local', 'testing'])`. **Jangan pernah** memanggil
`php artisan db:seed --class=DemoContentSeeder` di server — pemanggilan langsung
melewati pagar itu.

Akun dari `UserSeeder` adalah akun dummy tiap peran. Ganti password semuanya,
atau buat akun pengurus asli lalu hapus yang dummy.

---

## 5. Cloudflare Tunnel

**Cloudflare Zero Trust → Networks → Tunnels** → tunnel yang sudah melayani
Coolify → **Public Hostnames → Add a public hostname:**

| Field | Nilai |
|---|---|
| Subdomain | `masjid` |
| Domain | `iqbalfhz.my.id` |
| Type | `HTTP` |
| URL | `localhost:8087` |

TLS berakhir di Cloudflare. Dari `cloudflared` ke aplikasi lalu lintasnya HTTP
biasa, tapi tidak pernah keluar dari server — karena itu aplikasi butuh
`TRUSTED_PROXIES` untuk tahu bahwa pengunjungnya datang lewat HTTPS.

Tanpa rute ini, Cloudflare mencoba menyambung langsung ke IP publik server dan
gagal. Gejala yang pernah muncul: `coolify.iqbalfhz.my.id` terbuka, sedangkan
`masjid.iqbalfhz.my.id` tidak — karena hanya yang pertama punya rute di tunnel.

HTTPS bukan sekadar praktik baik di sini: **Web Push tidak bekerja tanpanya**,
karena browser menolak Service Worker di koneksi tidak aman.

---

## 6. Scheduled Task — Pengganti Cron

**Configuration → Scheduled Tasks → + Add:**

| Field | Nilai |
|---|---|
| Name | bebas, mis. `Scheduler Laravel` |
| Command | `php artisan schedule:run` |
| Frequency | `* * * * *` |
| Timeout (seconds) | `300` (bawaan) |
| Container name | kosong — aplikasi ini hanya satu container |

| Perintah | Jadwal | Kalau tidak jalan |
|---|---|---|
| `masjid:sync-prayer-schedules` | Harian 01:30 | Jadwal sholat di website publik lama-lama habis |
| `masjid:send-prayer-reminders` | Tiap menit | Pengingat berhenti terkirim ke jamaah |

> **Kegagalannya tidak bersuara.** Bila scheduler mati, tidak ada pesan error di
> mana pun. Yang pertama menyadari adalah jamaah yang salah datang waktu subuh.

### Memastikan scheduler benar-benar jalan

1. **Laravel mengenali tugasnya** — di Terminal: `php artisan schedule:list`.
   Kedua perintah di atas harus terdaftar.
2. **Coolify benar-benar menjalankannya** — buka Scheduled Task tersebut dan
   lihat riwayat eksekusinya. Harus ada entri setiap menit dengan status
   berhasil. **Ini satu-satunya bukti langsung.**

Kartu **Kesehatan Sistem** di Dashboard membantu, tapi perlu dibaca dengan benar:

- **Jadwal sholat tersedia** menunjukkan sampai tanggal berapa data jadwal ada.
  Sinkronisasi mengambil bulan ini ditambah dua bulan ke depan
  (`PRAYER_API_SYNC_MONTHS=2`), jadi satu kali jalan — termasuk yang manual di
  bagian 4.5 — sudah menghasilkan angka seperti "79 hari lagi, sampai 30
  November". Angka itu **berkurang satu setiap hari walaupun scheduler sehat**,
  lalu melonjak sekitar 30 hari tiap tanggal 1 setelah pukul 01:30. Bila pada
  tanggal 2 angkanya tidak melonjak, scheduler mati. Sinyal ini lambat: dari
  kartu ini saja, scheduler yang mati paling cepat ketahuan di awal bulan
  berikutnya.
- **Pengingat sholat** berubah menjadi "Terakhir terkirim …" setelah Web Push
  aktif dan ada jamaah berlangganan — sinyal yang jauh lebih cepat, karena
  diperbarui setiap waktu sholat.

### Queue worker tidak diperlukan

Keputusan sadar. Bergantung pada worker berarti fitur diam-diam berhenti setiap
kali worker mati:

- **Notifikasi internal** dikirim langsung lewat
  `App\Support\ImmediateDatabaseNotification` — menyimpannya hanya satu INSERT.
- **Export rekap** dijalankan `sync` supaya berkas jadi saat itu juga.
- **Reminder Web Push** berjalan di proses scheduler.

Karena tidak ada yang diantrekan, nilai `QUEUE_CONNECTION` tidak berpengaruh.
Kalau nanti ada pekerjaan berat baru (misalnya email massal), barulah siapkan
worker — dan ingat bahwa kegagalannya senyap.

---

## 7. Auto-deploy dari GitHub

1. Coolify → aplikasi → **Webhooks → Manual Git Webhooks → GitHub**. Salin
   URL-nya (`https://<alamat-coolify>/webhooks/source/github/events/manual`)
   beserta **GitHub Webhook Secret** di sebelahnya.
2. GitHub → repository → **Settings → Webhooks → Add webhook:**
   - **Payload URL**: URL dari langkah 1
   - **Content type**: `application/json` — bukan `form-urlencoded`
   - **Secret**: GitHub Webhook Secret dari Coolify
   - **Which events**: *Just the push event*
3. Kembali ke Coolify, pastikan **Auto Deploy** aktif.

Yang di atasnya — *Deploy Webhook (auth required)* — untuk dipanggil dari skrip
atau CI dengan Bearer token; tidak dipakai di alur ini.

**Konsekuensinya: setiap push ke `main` adalah deploy produksi.** Jalankan
`php artisan test --compact` sebelum push. Migrasi ikut berjalan otomatis saat
container start, jadi push yang membawa migrasi langsung mengubah database —
backup dulu (bagian 10).

Karena port host `127.0.0.1:8087` tidak bisa dipakai dua container sekaligus,
container lama berhenti sebelum yang baru menyala. Ada jeda singkat tanpa
layanan setiap kali deploy.

---

## 8. Web Push (Pengingat Sholat)

1. Buat sepasang kunci baru, di lokal atau di Terminal Coolify:

   ```bash
   php artisan masjid:vapid-keys
   ```

   Perintah ini hanya **mencetak** dua baris `VAPID_PUBLIC_KEY=...` dan
   `VAPID_PRIVATE_KEY=...`, tanpa menulis ke berkas mana pun. Buat pasangan
   khusus produksi — jangan memakai ulang kunci dari `.env` lokal.
2. Isi `VAPID_PUBLIC_KEY`, `VAPID_PRIVATE_KEY`, dan `VAPID_SUBJECT` di
   Environment Variables, lalu **Restart**. `VAPID_SUBJECT` diisi alamat email
   yang benar-benar ada, format `mailto:nama@domain` — layanan push memeriksa
   nilai ini, dan bawaan `mailto:admin@masjidannur.test` bukan alamat sungguhan.
3. Dashboard → Kesehatan Sistem → kartu **Pengingat sholat** berubah dari
   "Belum aktif — Kunci VAPID belum dibuat" menjadi "Dimatikan".
4. Admin panel → **Pengaturan Umum → tab Jadwal Sholat & Reminder** → nyalakan
   **Aktifkan reminder push notification**, centang waktu sholat yang boleh
   diingatkan, lalu **Simpan pengaturan**. Kartu berubah menjadi "0 jamaah
   berlangganan".

   Urutannya penting: toggle ini yang memunculkan tombol di halaman publik,
   sedangkan kunci VAPID yang membuat tombolnya bekerja. Toggle yang dinyalakan
   sebelum kunci terpasang menghasilkan tombol yang hanya menjawab "Pengingat
   belum diaktifkan pengurus masjid".
5. Uji dari HP Android (Chrome): buka `/jadwal-sholat` → bagian **Pengingat
   waktu sholat** → pilih waktu sholat dan jeda menit → **Aktifkan pengingat
   sholat** → izinkan notifikasi. Kartu di Dashboard menjadi "1 jamaah
   berlangganan", lalu "Terakhir terkirim …" setelah notifikasi pertama sampai.

6. Uji dari iPhone (Safari): buka situsnya, ketuk tombol **Bagikan** →
   **Tambahkan ke Layar Utama**, lalu buka lewat ikon yang baru muncul. Dari
   sana `/jadwal-sholat` bekerja sama seperti di Android. Selama dibuka di tab
   Safari biasa, halaman itu menampilkan petunjuk ini alih-alih tombol aktif —
   iOS memang hanya membuka Web Push untuk web app yang terpasang.

Jeda pengingat dipilih masing-masing jamaah di halaman publik (5, 10, 15, atau
30 menit). Nilai bawaannya diambil dari **Jeda pengingat bawaan (menit)** di
Pengaturan Umum.

> **Nada dering tidak bisa diatur dari aplikasi.** Web Push tidak punya opsi
> suara; yang menentukan berbunyi atau senyap adalah saluran notifikasi browser
> di HP (Setelan → Aplikasi → Chrome → Notifikasi → nama situs). Notifikasi yang
> masuk tanpa suara hampir selalu karena saluran itu disetel senyap.

> **Kunci VAPID hanya dibuat sekali.** Menggantinya membuat seluruh langganan
> jamaah yang sudah terdaftar tidak sah, dan mereka harus berlangganan ulang
> satu per satu. Kunci privat tidak boleh di-commit; simpan salinannya bersama
> `APP_KEY`.

---

## 9. Verifikasi Setelah Deploy

Ini daftar yang benar-benar pernah gagal, bukan formalitas.

| # | Periksa | Cara | Lolos bila |
|---|---|---|---|
| 1 | Halaman publik hidup | Buka `https://domain` | Beranda tampil, waktu sholat **sesuai jam dinding** |
| 2 | `.env` tidak terekspos | Buka `https://domain/.env` | 404 |
| 3 | Debug mati | Buka URL ngawur, mis. `/xyz` | Halaman 404 biasa, bukan jejak error Laravel |
| 4 | Aset terbangun | Lihat tampilan publik dan admin | Keduanya bergaya, bukan HTML polos |
| 5 | Login admin | `https://domain/admin` | Bisa masuk |
| 6 | Volume bekerja | Unggah 1 foto galeri, lalu **Redeploy** | Foto masih ada setelah redeploy |
| 7 | Pratinjau unggahan | Buka form ubah yang sudah bergambar | Gambar tampil, bukan kotak abu-abu |
| 8 | **Scheduler hidup** | Riwayat eksekusi Scheduled Task | Ada entri tiap menit, berhasil |
| 9 | Notifikasi sampai | Kirim 1 testimoni dari form publik | Lonceng sekretaris bertambah |
| 10 | Tautan notifikasi benar | Klik "Lihat detail" | Masuk ke halaman yang sesuai |
| 11 | Peran sesuai matriks | Login tiap peran | Menu & dashboard sesuai PRD 5.3 / 5.4 |
| 12 | Export jalan | Keuangan → Export | Berkas terunduh |
| 13 | Header keamanan | securityheaders.com | Semua header terdeteksi (saat ini A+) |
| 14 | Port tertutup dari jaringan lokal | Di server, lihat perintah di bawah | Loopback 200, IP LAN gagal |
| 15 | Tahan reboot | Reboot VM, tunggu, buka situs | Situs hidup sendiri |

Untuk poin 14:

```bash
docker ps --format "{{.Names}}\t{{.Ports}}" | grep 8087     # harus 127.0.0.1:8087->80/tcp
curl -I http://127.0.0.1:8087/                              # harus 200
curl -I --max-time 5 http://<IP-LAN-server>:8087/           # harus GAGAL tersambung
```

---

## 10. Update, Backup, dan Rollback

**Update** cukup dengan push ke `main` (bagian 7). Tidak ada perintah manual:
entrypoint menjalankan migrasi dan cache setiap container start.

**Backup** — dua hal yang tidak bisa dibuat ulang: isi database dan berkas
unggahan. Kodenya selalu bisa di-clone lagi.

### Database — lewat Coolify

Resource MySQL → tab **Backups** → tambahkan jadwal:

| Field | Nilai |
|---|---|
| Frequency | `0 2 * * *` (harian, pukul 02.00) |
| Number of backups to keep | 14 |
| Save to S3 | opsional, tapi inilah yang membuat backup selamat dari matinya server |

Klik **Backup Now** sekali untuk membuktikan jadwalnya bekerja; entri yang
berhasil muncul di daftar beserta ukurannya.

> **Jadwal ini dibaca sebagai UTC.** Kolom **Timezone** pada form backup tidak
> bisa diisi di Coolify 4.1.2, dan setelan *Instance Timezone* tidak
> memengaruhinya. Jadi `0 2 * * *` berarti 02.00 UTC — 09.00 WIB. Dibiarkan
> begitu tidak masalah: dump-nya beberapa ratus kilobyte dan selesai dalam
> hitungan detik, jadi jam pelaksanaannya tidak berpengaruh pada apa pun. Bila
> tetap ingin dini hari waktu Indonesia, isi Frequency `0 19 * * *`
> (19.00 UTC = 02.00 WIB keesokan harinya).
>
> Dua backup lainnya tidak terpengaruh: cron host dan jadwal Proxmox memakai jam
> mesin, yang sudah WIB.

### Berkas unggahan — skrip di host

Coolify tidak mencadangkan volume. Simpan skrip berikut sebagai
`/usr/local/bin/backup-masjid.sh`, lalu `chmod +x`:

> Tidak perlu SSH: menu **Terminal** di Coolify bisa dipakai, asalkan yang
> dipilih **server**-nya, bukan container aplikasi. Skrip ini memanggil `docker`
> dan membaca lokasi volume di host — keduanya tidak ada di dalam container.

```sh
#!/bin/sh
set -eu

# Arsip berkas unggahan Masjid An-Nur (isi volume masjid-storage).
# Mengabari lewat Telegram hanya bila gagal — keberhasilan tidak dilaporkan
# supaya pesannya tidak berubah jadi kebisingan yang diabaikan.

TUJUAN=/backup/masjid
SIMPAN_HARI=14
MIN_BYTE=102400            # arsip di bawah 100 KB dianggap mencurigakan
KONFIG=/etc/masjid-backup.env

kabari() {
    [ -f "$KONFIG" ] || return 0
    . "$KONFIG"
    [ -n "${TELEGRAM_TOKEN:-}" ] && [ -n "${TELEGRAM_CHAT_ID:-}" ] || return 0
    curl -sS --max-time 20 -o /dev/null \
        -d "chat_id=${TELEGRAM_CHAT_ID}" \
        --data-urlencode "text=$1" \
        "https://api.telegram.org/bot${TELEGRAM_TOKEN}/sendMessage" || true
}

gagal() {
    echo "GAGAL: $1" >&2
    kabari "Backup berkas unggahan masjid GAGAL di $(hostname): $1"
    exit 1
}

VOLUME=$(docker volume ls --quiet --filter name=masjid-storage | head -1) || gagal "docker tidak bisa dijalankan"
[ -n "$VOLUME" ] || gagal "volume masjid-storage tidak ditemukan"

SUMBER=$(docker volume inspect "$VOLUME" --format '{{ .Mountpoint }}') || gagal "lokasi volume tidak terbaca"

mkdir -p "$TUJUAN" || gagal "folder $TUJUAN tidak bisa dibuat"
ARSIP="$TUJUAN/storage-$(date +%F).tar.gz"

tar -czf "$ARSIP" -C "$SUMBER" . || gagal "tar gagal membuat arsip (disk penuh?)"

UKURAN=$(stat -c %s "$ARSIP")
[ "$UKURAN" -ge "$MIN_BYTE" ] || gagal "arsip hanya $UKURAN byte, jauh lebih kecil dari biasanya"

find "$TUJUAN" -name 'storage-*.tar.gz' -mtime +$SIMPAN_HARI -delete

echo "OK: $ARSIP ($UKURAN byte)"
```

Nama volumenya dicari otomatis karena Coolify memberinya awalan ID aplikasi
(`<uuid>-masjid-storage`) yang berubah bila aplikasinya dibuat ulang.

Kredensial Telegram disimpan terpisah supaya skripnya aman disalin ke mana pun:

```bash
cat > /etc/masjid-backup.env <<'EOF'
TELEGRAM_TOKEN=token-bot-anda
TELEGRAM_CHAT_ID=chat-id-anda
EOF

chmod 600 /etc/masjid-backup.env
```

Tanpa berkas itu skripnya tetap berjalan, hanya tidak mengabari siapa pun.

```cron
15 2 * * * /usr/local/bin/backup-masjid.sh >> /var/log/backup-masjid.log 2>&1
```

### Bawa keluar dari server

Backup yang tersimpan di disk yang sama dengan sumbernya hanya melindungi dari
kesalahan manusia, bukan dari matinya server. Pilih salah satu: backup VM
Proxmox (vzdump) yang mencakup keduanya sekaligus, S3 pada backup database, atau
salin `/backup` ke mesin lain secara berkala.

**Backup VM di Proxmox** (Datacenter → Backup): pilih hanya VM Coolify,
Mode `Snapshot`, kompresi `ZSTD`, jadwal `03:00`, retensi `keep-daily=7`
ditambah `keep-weekly=4`. Pastikan QEMU Guest Agent terpasang di dalam VM —
lognya akan menyebut `fs-freeze`, tanda sistem berkas dibekukan sesaat
sehingga hasilnya jauh lebih rapi daripada sekadar salinan mentah.

Ukuran sebenarnya, diukur 12 September 2026: boot disk 1 TB yang 94% kosong
menghasilkan arsip **16,81 GB dalam 36 menit**. Dengan retensi di atas,
kebutuhan puncaknya sekitar 185 GB.

### Pemberitahuan saat gagal

Backup yang gagal tanpa suara sama saja dengan tidak punya backup. Ketiga
lapisan dihubungkan ke satu bot Telegram, dan **hanya kegagalan yang dikirim** —
laporan sukses harian hanya membuat pesannya berhenti dibaca.

| Lapisan | Jalur pemberitahuan |
|---|---|
| Database | Coolify → **Notifications → Telegram**: centang *Backup Failure*, *Deployment Failure*, *Scheduled Task Failure* |
| Berkas unggahan | Skrip di atas membacanya dari `/etc/masjid-backup.env` dan mengirim sendiri |
| VM | Proxmox → **Datacenter → Notifications**: target **Webhook** ke API Telegram, plus matcher severity `error, warning` |

Isi target webhook Proxmox:

| Field | Nilai |
|---|---|
| Method/URL | `POST` — `https://api.telegram.org/bot{{ secrets.token }}/sendMessage` |
| Headers | `Content-Type` = `application/json` |
| Body | `{"chat_id":"<chat id>","text":"{{ escape title }}\n\n{{ escape message }}"}` |
| Secrets | `token` = token bot |

Token ditaruh di bagian **Secrets**, bukan langsung di URL, supaya tidak terbaca
lagi setelah tersimpan. `{{ escape … }}` mencegah tanda kutip di dalam pesan
Proxmox merusak struktur JSON-nya.

Matcher-nya dibuat terpisah (`telegram-gagal`) dengan aturan **Match Severity:
error, warning**, dan pada tab *Targets to notify* centang target `telegram`.
Tanpa matcher, target yang sudah benar pun tidak menerima apa-apa.

**Uji jalur gagalnya, bukan hanya jalur suksesnya.** Untuk skrip unggahan,
jalankan salinannya dengan ambang ukuran yang mustahil dipenuhi — skrip aslinya
tidak tersentuh:

```bash
sed 's/^MIN_BYTE=.*/MIN_BYTE=999999999/' /usr/local/bin/backup-masjid.sh | sh
```

Pesan kegagalan harus masuk ke Telegram dalam hitungan detik. Untuk Proxmox,
tombol **Test** pada target webhook melakukan hal yang sama.
### Kunci yang ikut menentukan

`APP_KEY` dan kunci VAPID disimpan sebagai environment variable, bukan di dalam
database maupun volume — jadi keduanya **tidak ikut tercadangkan**. Simpan
salinannya di pengelola kata sandi. Tanpa `APP_KEY` yang sama, database hasil
restore tidak bisa membaca data terenkripsinya; tanpa kunci VAPID yang sama,
seluruh langganan pengingat sholat harus didaftarkan ulang.

### Uji restore

> Backup yang belum pernah dicoba dipulihkan belum tentu backup.

Database — pulihkan ke database uji, bukan ke database produksi:

```bash
docker exec -i <container-mysql> mysql -uroot -p'<root-password>' -e 'CREATE DATABASE uji_restore;'
gunzip -c <berkas-backup>.sql.gz | docker exec -i <container-mysql> mysql -uroot -p'<root-password>' uji_restore
docker exec -i <container-mysql> mysql -uroot -p'<root-password>' -e 'SELECT COUNT(*) FROM uji_restore.users;'
docker exec -i <container-mysql> mysql -uroot -p'<root-password>' -e 'DROP DATABASE uji_restore;'
```

Berkas unggahan — periksa isinya dulu, baru pulihkan bila memang perlu:

```bash
tar -tzf /backup/masjid/storage-<tanggal>.tar.gz | head
docker run --rm -v <nama-volume>:/data -v /backup/masjid:/backup alpine \
    sh -c 'tar -xzf /backup/storage-<tanggal>.tar.gz -C /data'
```

**Rollback:** Coolify menyimpan image dari deploy sebelumnya, dan menu
**Rollback** bisa menyalakannya kembali. Migrasi database **tidak** ikut mundur —
karena itu backup sebelum push yang membawa migrasi.

---

## 11. Memasang untuk Masjid Lain

Kodebase ini dirancang ulang-pakai (PRD bagian 13). Untuk masjid kedua di server
yang sama:

1. Buat resource MySQL baru (bagian 3).
2. Buat aplikasi baru dari repository yang sama (bagian 4), dengan:
   - `APP_KEY` **baru**, `APP_NAME`, `APP_URL`, kredensial DB, dan
     **`APP_TIMEZONE`** sesuai zona masjid tersebut;
   - `PRAYER_API_LATITUDE` / `PRAYER_API_LONGITUDE` sesuai koordinat masjid;
   - port berikutnya yang masih kosong, misalnya `127.0.0.1:8088:80`;
   - volume sendiri di `/app/storage/app/public`.
3. Tambahkan Public Hostname baru di tunnel menuju port tersebut (bagian 5).
4. Tambahkan Scheduled Task (bagian 6) dan kunci VAPID **baru** (bagian 8).
5. Deploy, lalu jalankan perintah data awal lewat Terminal (bagian 4.5).
6. Isi identitas, rekening donasi, QRIS, dan struktur pengurus lewat admin panel.

Tidak ada nilai khusus Masjid An-Nur yang tertanam di kode — semuanya lewat
environment atau tabel pengaturan.

### Tanpa Coolify

Image di repo ini berdiri sendiri — Coolify hanya pembungkusnya. Langkah
lengkap memasangnya di VPS biasa ada di bagian 15.

---

## 12. Troubleshooting Production

Log aplikasi ada di dalam container dan hilang saat redeploy. Baca lewat
Terminal: `tail -n 50 storage/logs/laravel.log`.

### Build & container

| Masalah | Penyebab & Solusi |
|---|---|
| Build gagal: `composer install` menolak karena `ext-intl` | Composer harus berjalan di PHP yang sama dengan runtime. Jangan kembali ke image `composer:2` di tahap `vendor` |
| Build gagal di tahap npm dengan `EAI_AGAIN` | Build mencoba mengunduh sesuatu selain paket npm. Jangan kembalikan plugin font online di `vite.config.js`; font ada di `resources/fonts/` |
| Container restart terus; log berisi `error parsing regexp` atau `invalid or unsupported Perl syntax` | Caddyfile memakai pola yang tidak didukung RE2 (mis. lookahead `(?!`). Hapus aturannya (bagian 2) |
| Deploy tak kunjung selesai / container `unhealthy` | Di Terminal: `curl -fsS http://127.0.0.1/up`. Bila gagal, lihat log container; bila berhasil, periksa setelan Healthcheck Coolify (path `/up`, port `80`) |
| Kode baru tidak aktif setelah push | Lihat GitHub → Settings → Webhooks → **Recent Deliveries**: harus 200. Pastikan Auto Deploy aktif. Jangan menyunting berkas lewat Terminal — opcache tidak membaca ulang |

### Akses & jaringan

| Masalah | Penyebab & Solusi |
|---|---|
| Container sehat tapi situs tidak menjawab / 502 | **Ports Exposes** bukan `80` |
| `coolify.<domain>` terbuka, `masjid.<domain>` tidak | Rute Public Hostname di tunnel belum ada (bagian 5) |
| Rute tunnel ada, tapi Cloudflare menampilkan error | Aplikasi tidak menjawab di `localhost:8087`. Di server: `curl -I http://127.0.0.1:8087/` dan `docker ps \| grep 8087`. Bila `cloudflared` bukan network `host`, lihat bagian 4.2 |
| Situs bisa dibuka dari jaringan lokal lewat `:8087` | Port Mappings masih `8087:80`. Ubah ke `127.0.0.1:8087:80`, lalu Redeploy |
| securityheaders.com tidak menemukan HSTS | Aplikasi tidak tahu dirinya diakses lewat HTTPS. Pastikan `TRUSTED_PROXIES` terisi, lalu Restart |

### Aplikasi

| Masalah | Penyebab & Solusi |
|---|---|
| Error 500 di semua halaman | Baca log. Paling sering: `APP_KEY` kosong atau `DB_HOST` salah |
| `SQLSTATE[HY000] [2002]` | `DB_HOST` bukan host internal MySQL, atau resource database belum Start |
| Perubahan environment tidak berpengaruh | Setelah Save perlu **Restart** — konfigurasi dibekukan saat container start |
| Admin panel tampil sebagai HTML polos | Aset Filament belum dibuat. Seharusnya dikerjakan entrypoint (`filament:assets`); periksa log container saat start |
| Gambar unggahan hilang setelah redeploy | Volume tidak terpasang (bagian 4.3). Berkas yang diunggah sebelum volume ada tidak bisa dipulihkan |
| Gambar unggahan 404 padahal berkasnya ada | Symlink `public/storage` hilang. Restart — entrypoint membuatnya ulang |
| Upload besar gagal | Batasnya 20 MB (`upload_max_filesize` di `Dockerfile`) |
| Tidak bisa login padahal akun ada | Password dari seeder tidak tercatat. Reset lewat Terminal — lihat di bawah |
| Menu admin tidak lengkap | `php artisan shield:generate --all --panel=admin` lalu `php artisan permission:cache-reset` |
| Jadwal sholat berhenti / "Habis" | Scheduler tidak jalan. Periksa riwayat Scheduled Task, lalu jalankan `php artisan masjid:sync-prayer-schedules` manual |
| Waktu sholat meleset berjam-jam | `APP_TIMEZONE` salah, atau belum Restart setelah diubah |
| Push notification tidak terkirim | Kunci VAPID kosong, pengingat belum diaktifkan di Pengaturan Umum, atau belum ada jamaah berlangganan |
| Lonceng notifikasi kosong | Cek `DB::table('jobs')->count()`. Bila menumpuk, ada kode yang mengantrekan notifikasi — seharusnya dikirim langsung |
| Tautan notifikasi salah alamat | `APP_URL` tidak sesuai domain produksi |

### Content-Security-Policy

| Masalah | Penyebab & Solusi |
|---|---|
| Fitur di halaman publik tidak bereaksi; konsol browser berisi `violates the following Content Security Policy` | Ada skrip atau atribut `on…=` inline yang diblokir CSP publik. Sementara: `CSP_REPORT_ONLY=true` (bagian 4.4). Permanen: pindahkan perilakunya ke `resources/js/app.js` |
| Peta di halaman kontak kosong | Tautan harus berupa embed Google Maps. Domain peta lain diblokir `frame-src` — tambahkan di `SecurityHeaders::PUBLIK` bila memang perlu |
| Pratinjau gambar di form admin berupa kotak abu-abu (nama berkas terlihat, gambarnya tidak) | CSP admin memblokir Web Worker `blob:` milik FilePond. Pastikan `worker-src 'self' blob:` ada di `SecurityHeaders::ADMIN` |

### Reset password pengurus

Di tab **Terminal** aplikasi:

```bash
php artisan tinker
```

```php
App\Models\User::where('email', 'email@pengurus')->firstOrFail()->update(['password' => 'password-baru-yang-kuat']);
```

Model `User` memakai cast `hashed`, jadi password otomatis di-hash — jangan
membungkusnya dengan `Hash::make()`. Setelah berhasil masuk, minta pengurus
menggantinya lagi lewat halaman **Profil**.

---

## 13. Ringkasan Keamanan

- [ ] `APP_DEBUG=false` dan `APP_ENV=production`
- [ ] `APP_KEY` terisi dan salinannya tersimpan di luar server
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=local` — berkas unggahan sementara tidak publik
- [ ] Port aplikasi terikat ke `127.0.0.1` — cek dengan `docker ps` (bagian 9 poin 14)
- [ ] MySQL: Ports Mappings kosong, *Make it publicly available* tidak dicentang
- [ ] Password akun dummy sudah diganti atau akunnya dihapus
- [ ] Password database kuat dan berbeda dari akun lain
- [ ] `DemoContentSeeder` tidak pernah dijalankan di server
- [ ] Volume `masjid-storage` terpasang di `/app/storage/app/public`
- [ ] Backup database terjadwal, isi volume ikut dicadangkan, dan **pernah diuji restore**
- [ ] Header keamanan terkirim — securityheaders.com (saat ini A+)
- [ ] `CSP_REPORT_ONLY` tidak diisi; `true` hanya untuk keadaan darurat
- [ ] `TRUSTED_PROXIES=*` hanya bersama ikatan `127.0.0.1`
- [ ] Kunci VAPID privat dan `APP_KEY` tidak pernah di-commit
- [ ] Webhook GitHub memakai secret yang sama dengan di Coolify

Rate limiting form publik (testimoni, saran, RSVP, pendaftaran) sudah aktif dari
kode sesuai PRD bagian 6 — tidak perlu konfigurasi tambahan di server.

---

## 14. Pemeliharaan Rutin

Setelah semuanya berjalan, pekerjaan yang tersisa sedikit — tapi tidak nol.
Daftar ini sengaja pendek supaya benar-benar dikerjakan.

### Otomatis, tanpa Anda sentuh

| Kapan | Apa |
|---|---|
| Tiap menit | Scheduler mengirim pengingat sholat bagi jamaah yang berlangganan |
| Tiap hari 01.30 | Sinkronisasi jadwal sholat tiga bulan ke depan |
| Tiap hari | Tiga lapisan backup (bagian 10) |
| Tiap push ke `main` | Build dan deploy, termasuk migrasi database |

Semua kegagalannya dikabarkan ke Telegram. **Tidak ada kabar berarti tidak ada
masalah** — bukan berarti tidak ada yang berjalan; itulah sebabnya pemeriksaan
di bawah tetap diperlukan.

### Mingguan, sekitar dua menit

1. Buka **Dashboard** admin, lihat kartu **Kesehatan Sistem**: sisa hari jadwal
   sholat masih wajar, dan pengingat masih terkirim.
2. Buka daftar **Executions** backup database di Coolify: entri terbaru harus
   dari hari ini.
3. Di Terminal server: `ls -lh /backup/masjid | tail -3` — arsip terbaru harus
   bertanggal hari ini dan ukurannya tidak menyusut drastis.

### Bulanan, sekitar lima belas menit

1. **Uji restore** satu backup ke database uji (prosedurnya di bagian 10). Ini
   satu-satunya cara mengetahui backup Anda benar-benar bisa dipakai.
2. Periksa ruang disk: `df -h /` di server Coolify dan `df -h /var/lib/vz` di
   Proxmox.
3. Tinjau akun pengurus: hapus yang sudah tidak aktif, pastikan tidak ada akun
   dummy yang tersisa.
4. Periksa pembaruan Coolify dan Proxmox, lalu pasang di waktu sepi.

### Saat ada yang berubah di masjid

| Perubahan | Yang perlu disesuaikan |
|---|---|
| Pengurus baru / pengurus keluar | Menu **Pengguna** di admin panel |
| Pindah lokasi atau koreksi titik | Koordinat di **Pengaturan Umum** — jadwal sholat mengikutinya |
| Ganti rekening atau QRIS | **Pengaturan Umum → Donasi** |
| Ganti domain | `APP_URL` di Coolify, rute Public Hostname di Cloudflare, lalu Restart |

### Yang jangan dilakukan

- **Menyunting berkas lewat Terminal container.** Opcache tidak membacanya, dan
  perubahannya hilang di deploy berikutnya. Semua perubahan kode lewat Git.
- **Menjalankan `DemoContentSeeder` di server.** Ia mengisi database dengan
  konten contoh.
- **Mengganti kunci VAPID** tanpa alasan kuat. Seluruh langganan pengingat jamaah
  langsung tidak sah dan harus didaftarkan ulang satu per satu.


---

## 15. Memasang Tanpa Coolify

Bagian 1–14 mengikuti instalasi yang benar-benar berjalan, dan instalasi itu
memakai Coolify. Tapi Coolify hanya pembungkus: yang melayani jamaah adalah
image Docker di repo ini. Bagian ini menjelaskan cara menjalankannya di VPS
biasa, untuk siapa pun yang ingin memasang sistem ini di masjidnya sendiri.

> **Belum diuji.** Perintah di bawah diturunkan dari apa yang Coolify kerjakan —
> bisa ditelusuri sendiri di bagian 2 dan 4. Semuanya perintah Docker standar,
> tapi penulis dokumen ini tidak menjalankannya langsung. Perlakukan sebagai
> titik awal yang masuk akal, bukan resep yang sudah terbukti.

### Yang dibutuhkan

| Komponen | Catatan |
|---|---|
| Docker Engine 24+ | Beserta plugin `compose` bila memakai berkas compose |
| MySQL 8 | Boleh container seperti contoh di bawah, boleh layanan terkelola |
| Nama domain | Hanya bila ingin HTTPS otomatis tanpa proxy di depan |
| RAM 2 GB | Build sempat memakan lebih banyak daripada saat melayani |

### 1. Ambil kode dan siapkan environment

```bash
git clone https://github.com/iqbalfhz/masjid.git
cd masjid
cp .env.example .env.production
```

Isi `.env.production` mengikuti daftar di bagian 4.4. Nilai yang wajib
disesuaikan: `APP_KEY`, `APP_URL`, `APP_TIMEZONE`, kredensial database,
`SESSION_SECURE_COOKIE=true`, dan `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=local`.

`APP_KEY` bisa dibuat tanpa memasang PHP di server, setelah image dibangun:

```bash
docker run --rm masjid php artisan key:generate --show
```

> **Jangan pakai tanda kutip di berkas env Docker.** Tulis
> `APP_NAME=Masjid An-Nur`, bukan `APP_NAME="Masjid An-Nur"` — Docker
> memperlakukan tanda kutipnya sebagai bagian dari nilai, dan nama masjid akan
> tampil lengkap dengan tanda kutip di seluruh halaman.

### 2. Bangun image

```bash
docker build -t masjid .
```

### 3. Jalankan dengan compose

Simpan sebagai `compose.yaml` di sebelah `Dockerfile`:

```yaml
services:
  app:
    build: .
    restart: unless-stopped
    env_file: .env.production
    ports:
      # Ganti menjadi "127.0.0.1:8080:80" bila ada proxy atau tunnel di depannya.
      - "80:80"
    volumes:
      - unggahan:/app/storage/app/public
    depends_on:
      - db

  db:
    image: mysql:8
    restart: unless-stopped
    command: --character-set-server=utf8mb4 --collation-server=utf8mb4_unicode_ci
    environment:
      MYSQL_DATABASE: masjid_annur
      MYSQL_USER: masjid
      MYSQL_PASSWORD: ganti-dengan-password-kuat
      MYSQL_ROOT_PASSWORD: ganti-juga-yang-ini
    volumes:
      - basisdata:/var/lib/mysql

volumes:
  unggahan:
  basisdata:
```

Dengan susunan ini, `DB_HOST` di `.env.production` diisi `db` — nama layanannya,
bukan `localhost`. Lalu:

```bash
docker compose up -d
docker compose logs -f app
```

Entrypoint menjalankan migrasi dan menyiapkan cache setiap container start, jadi
tidak ada langkah manual untuk itu.

> **Nama volume menentukan hidup-matinya data.** Bila suatu saat berkas compose
> ini diubah atau dipindah, pastikan nama volumenya tetap sama. Volume dengan
> nama berbeda dibuat kosong, dan seluruh unggahan akan tampak lenyap padahal
> datanya masih ada di volume lama.

### 4. Isi data awal

Sekali saja, setelah container berjalan:

```bash
docker compose exec app php artisan db:seed --force
docker compose exec app php artisan shield:generate --all --panel=admin
docker compose exec app php artisan permission:cache-reset
docker compose exec app php artisan masjid:sync-prayer-schedules
```

`db:seed` mencetak password akun pengurus **satu kali** — catat saat itu juga.

### 5. HTTPS

Dua jalur, pilih salah satu:

**Di balik proxy atau tunnel** (nginx, Caddy, Traefik, Cloudflare Tunnel):
biarkan `SERVER_NAME` apa adanya (`:80`), ikat port ke `127.0.0.1`, dan isi
`TRUSTED_PROXIES` — tanpa itu aplikasi mengira dirinya diakses lewat HTTP dan
membangun tautan yang salah (bagian 4.4).

**Tanpa proxy:** tambahkan `SERVER_NAME=masjid.contoh.or.id` ke environment dan
petakan port 80 dan 443. FrankenPHP menerbitkan sertifikat Let's Encrypt sendiri
— tidak perlu Certbot. Syaratnya domain sudah mengarah ke server dan port 80
terbuka, karena dipakai untuk tantangan ACME.

### 6. Pekerjaan terjadwal

Tidak ada Scheduled Task di sini, jadi pakai cron milik host:

```cron
* * * * * cd /path/ke/proyek && docker compose exec -T app php artisan schedule:run >> /dev/null 2>&1
```

`-T` penting: tanpa itu cron gagal karena tidak punya terminal. Cara
memastikannya benar-benar jalan ada di bagian 6.

### 7. Backup

Prinsip dan skripnya sama seperti bagian 10, dengan dua penyesuaian:

- Pada skrip arsip unggahan, ganti filter nama volumenya menjadi `unggahan`
  (atau nama yang Anda pakai).
- Database tidak lagi dicadangkan Coolify, jadi jadwalkan sendiri:

  ```cron
  0 2 * * * cd /path/ke/proyek && docker compose exec -T db mysqldump -u root -p'password-root' masjid_annur | gzip > /backup/db-$(date +\%F).sql.gz
  ```

Uji restore-nya tetap wajib — prosedurnya di bagian 10.

### 8. Update

```bash
git pull
docker compose build
docker compose up -d
```

Migrasi dan cache dikerjakan entrypoint saat container baru menyala. Backup
database dulu bila pembaruannya membawa migrasi.
