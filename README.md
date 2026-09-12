# Sistem Informasi Masjid An-Nur

[![tests](https://github.com/iqbalfhz/masjid/actions/workflows/tests.yml/badge.svg)](https://github.com/iqbalfhz/masjid/actions/workflows/tests.yml)
[![license: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

Website publik dan panel pengurus untuk Masjid An-Nur, Tangcity Mall — jadwal
sholat, kajian, laporan keuangan, galeri, layanan jamaah, dan pengelolaannya
oleh Tim DKM tanpa perlu kemampuan teknis.

Dibangun untuk **dipasang ulang per masjid**, bukan multi-tenant: tidak ada
nilai khusus An-Nur yang tertanam di kode. Nama, alamat, koordinat, zona waktu,
rekening donasi, dan identitas lain diatur lewat environment atau menu
Pengaturan Umum.

Produksi: <https://masjid.iqbalfhz.my.id>

## Dokumentasi

| Dokumen | Untuk siapa |
|---|---|
| [PRD](doks/PRD-Website-Masjid-An-Nur.md) | Apa yang dibangun, untuk siapa, dan mengapa — termasuk keputusan serta improvisasi yang diambil selama pengerjaan |
| [Tutorial Instalasi Lokal](doks/Tutorial-Instalasi-Lokal.md) | Developer yang menyiapkan lingkungan kerja di mesinnya sendiri |
| [Panduan Deploy Production](doks/Panduan-Deploy-Production.md) | Orang yang memasang, merawat, mencadangkan, dan memulihkan sistem di server |

Ketiganya ditulis berbahasa Indonesia dan diperbarui bersamaan dengan kodenya.
Bila suatu perubahan menyimpang dari rancangan awal, alasannya dicatat di PRD.

## Tumpukan teknologi

| Lapisan | Teknologi |
|---|---|
| Backend | Laravel 13, PHP 8.4 |
| Panel pengurus | Filament 5 (+ Shield untuk role & permission) |
| Halaman publik | Blade server-rendered, Tailwind CSS 4, Vite |
| Database | MySQL 8 |
| Pencarian | Laravel Scout, driver database |
| Jadwal sholat | Aladhan API, disinkronkan terjadwal |
| Pengingat sholat | Web Push (VAPID) + service worker |
| Runtime produksi | FrankenPHP di dalam Docker, dipasang lewat Coolify, diakses via Cloudflare Tunnel |

## Mulai cepat (lokal)

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run dev
```

Langkah lengkapnya — database, Filament Shield, jadwal sholat pertama, Web Push,
dan scheduler — ada di [Tutorial Instalasi Lokal](doks/Tutorial-Instalasi-Lokal.md).

## Menjalankan test

```bash
php artisan test --compact
```

Test bukan pelengkap di proyek ini: sebagian besar menjaga jebakan yang pernah
benar-benar terjadi — notifikasi yang mengendap di antrean, tag yang hilang
diam-diam saat disimpan, skrip yang diblokir Content-Security-Policy, relasi yang
lupa di-eager load. Penjelasan tiap kasus ada di komentar test-nya.

## Struktur singkat

| Lokasi | Isi |
|---|---|
| `app/Filament/` | Panel pengurus: resource, halaman, widget dashboard |
| `app/Http/Controllers/` | Halaman publik |
| `app/Services/` | Jadwal sholat dan Web Push |
| `app/Support/` | Perkakas lintas modul (pengiriman notifikasi langsung, antrean approval) |
| `resources/views/public/` | Tampilan halaman publik |
| `docker/` | Caddyfile dan entrypoint image produksi |
| `doks/` | Dokumentasi |

Peta yang lebih rinci ada di bagian 18 Tutorial Instalasi Lokal.

## Memasang untuk masjid Anda

Sistem ini memang dimaksudkan untuk ditiru. Dua jalur tersedia:

- **Coolify** — jalur yang dipakai instalasi aslinya, lengkap dengan Cloudflare
  Tunnel, backup berlapis, dan pemberitahuan kegagalan lewat Telegram. Seluruh
  langkahnya ada di [Panduan Deploy](doks/Panduan-Deploy-Production.md).
- **Docker biasa di VPS mana pun** — bagian 15 panduan yang sama. Image di repo
  ini berdiri sendiri; Coolify hanya pembungkusnya.

Yang perlu diubah untuk masjid lain hanya environment dan menu Pengaturan Umum:
nama, alamat, koordinat, zona waktu, rekening donasi, dan kunci VAPID baru.
Rinciannya di bagian 11 Panduan Deploy.

## Lisensi

Kode sistem ini dirilis di bawah [Lisensi MIT](LICENSE) — bebas dipakai, diubah,
dan dipasang untuk masjid lain, selama pemberitahuan hak ciptanya disertakan.

Aset pihak ketiga yang ikut dibundel: font **Instrument Sans** di bawah
[SIL Open Font License 1.1](https://openfontlicense.org) — keterangannya di
`resources/fonts/README.md`. Jadwal sholat diambil dari
[Aladhan API](https://aladhan.com/prayer-times-api).

Konten masjid — foto, logo, artikel, dan data keuangan — **tidak** termasuk
dalam lisensi ini. Yang dibagikan adalah perangkat lunaknya, bukan isinya.
