# Product Requirements Document (PRD)
# Sistem Informasi Masjid An-Nur — Tangcity Mall

**Versi:** 2.0
**Tanggal:** 10 September 2026
**Disusun oleh:** Iqbal Fahrozi
**Status:** Draft untuk review Tim DKM

---

## 1. Latar Belakang

Masjid An-Nur berada di lingkungan Tangcity Mall dan melayani jamaah karyawan, pengunjung mall, dan masyarakat sekitar. Saat ini informasi seputar jadwal sholat, kajian, pengumuman, dan laporan keuangan masih disampaikan secara manual (grup WhatsApp, papan pengumuman fisik), sehingga:

- Informasi mudah tertinggal/tidak terupdate
- Laporan keuangan tidak mudah diakses jamaah secara transparan
- Tidak ada arsip digital kegiatan (dokumentasi, kajian, dll)
- Pengurus (DKM) kesulitan mengelola informasi tanpa alat bantu terpusat
- Belum ada kanal interaksi dua arah antara jamaah dan pengurus (saran, testimoni, pendaftaran layanan)

Dibutuhkan sebuah **website publik** yang informatif sekaligus interaktif sebagai kanal resmi, dan **admin panel** sebagai alat kerja Tim DKM untuk mengelola seluruh konten dan layanan tanpa perlu kemampuan teknis.

**Catatan arsitektur:** Sistem ini dirancang sebagai codebase yang **reusable** — meskipun dibangun pertama kali untuk Masjid An-Nur, struktur modul dan role dibuat cukup generik sehingga bisa di-deploy ulang (instalasi terpisah, bukan multi-tenant) untuk masjid lain di kemudian hari. Karena itu ada pemisahan jelas antara **Superadmin** (pemilik/pengelola teknis sistem, dalam hal ini Iqbal sebagai developer) dengan **Admin** (pengurus tertinggi di tiap masjid yang memakai sistem ini).

---

## 2. Tujuan Produk

1. Menyediakan informasi jadwal sholat, kajian, dan kegiatan masjid yang selalu up-to-date dan mudah diakses jamaah.
2. Meningkatkan transparansi keuangan masjid (infaq, donasi, pengeluaran) kepada jamaah.
3. Memberikan alat pengelolaan konten yang mudah dipakai oleh Tim DKM (non-teknis) tanpa bergantung pada developer.
4. Mendukung pengelolaan multi-user dengan pembagian peran (role) sesuai struktur kepengurusan DKM.
5. Menghadirkan interaksi dua arah antara jamaah dan pengurus (saran, testimoni, RSVP, pendaftaran layanan).
6. Menjadi identitas digital resmi Masjid An-Nur yang lengkap dan interaktif.

---

## 3. Target Pengguna

| Peran | Deskripsi | Kebutuhan Utama |
|---|---|---|
| **Jamaah / Pengunjung** | Karyawan, pengunjung mall, masyarakat umum | Info jadwal sholat, kajian, pengumuman, laporan keuangan, lokasi, layanan interaktif |
| **Superadmin** | Pemilik/pengelola teknis sistem (developer) | Akses penuh ke SEMUA menu tanpa batasan, termasuk hal-hal level sistem; bisa melihat & mengelola instalasi ini secara keseluruhan |
| **Admin (Pengurus Masjid)** | Pengurus tertinggi di masjid tsb (di atas Ketua DKM secara akses sistem) | Akses penuh ke semua modul operasional masjid (setara Superadmin dari sisi konten, tapi tanpa akses level sistem seperti pengaturan teknis) |
| **Ketua DKM** | Pimpinan pengurus harian | Overview modul, approval konten |
| **Sekretaris** | Mengelola info & pengumuman | Kelola pengumuman, kajian, artikel, galeri, FAQ, moderasi testimoni |
| **Bendahara** | Mengelola keuangan | Input & kelola laporan keuangan, rekap pendaftaran kurban/zakat |

*Hierarki akses: Superadmin > Admin (Pengurus Masjid) > Ketua DKM > Sekretaris / Bendahara (dua role terakhir sejajar, beda area tanggung jawab).*

---

## 4. Ruang Lingkup (Scope)

### 4.1 In-Scope
- Website publik (informasi + layanan interaktif masjid)
- Admin panel (CRUD semua konten & layanan, multi-role)
- Jadwal sholat otomatis (integrasi API) + reminder push notification
- Laporan keuangan (input manual oleh bendahara, ditampilkan publik, filterable)
- Info donasi (tampilan rekening & QRIS statis — tanpa payment gateway)
- Manajemen pengumuman, kajian, artikel, galeri, e-library, profil pengurus
- Interaksi jamaah: testimoni, kotak saran, RSVP kajian
- Pendaftaran layanan: kurban/aqiqah, zakat, peminjaman fasilitas (pembayaran/konfirmasi tetap manual)
- FAQ, statistik pencapaian, pencarian global, tag lintas modul
- Dokumentasi instalasi (setup lokal untuk development, dan deploy ke production) agar codebase mudah di-reuse untuk masjid lain

### 4.2 Out-of-Scope (Fase 1-3)
- Payment gateway / pembayaran online real-time (donasi, kurban, zakat, sewa fasilitas)
- Aplikasi mobile native
- Live streaming terintegrasi (cukup embed YouTube link)
- Multi-bahasa (bahasa Indonesia saja di fase awal)
- Notifikasi WhatsApp/email otomatis (fase depan, di luar scope awal)

---

## 5. Kebutuhan Fungsional

### 5.1 Website Publik

#### 5.1.1 Beranda
- Jadwal sholat hari ini (5 waktu + imsak) otomatis, dengan countdown ke waktu berikutnya
- Running text pengumuman terbaru
- Kajian & event terdekat
- Highlight laporan keuangan bulan berjalan
- Highlight galeri & artikel terbaru
- Counter statistik pencapaian (lihat 5.1.16)
- Kotak pencarian global (lihat 5.1.17)

#### 5.1.2 Jadwal Sholat
- Tabel jadwal sholat bulanan (auto-generate dari API, koordinat lokasi Tangcity)
- Jadwal khusus: Jumat, sholat Id, Tarawih (saat Ramadhan)
- Override manual oleh admin jika ada penyesuaian lokal
- **Reminder push notification**: jamaah bisa subscribe (opt-in browser notification) untuk diingatkan menjelang tiap waktu sholat

#### 5.1.3 Kajian & Kegiatan
- Daftar kajian rutin (ustadz, tema, hari/jam, lokasi) dan kalender kegiatan tahunan
- Detail per kegiatan: deskripsi, poster/flyer, tanggal
- **RSVP/konfirmasi kehadiran**: jamaah isi form singkat (nama, kontak, jumlah orang) untuk kajian/event tertentu — membantu panitia estimasi konsumsi & tempat

#### 5.1.4 Laporan Keuangan
- Tabel laporan bulanan: total pemasukan, pengeluaran, saldo akhir
- Rincian per kategori (master data, tidak hardcode)
- Filter multi-dimensi: rentang tanggal, kategori, jenis transaksi
- Unduh laporan hasil filter (PDF)

#### 5.1.5 Donasi
- Info rekening bank & QRIS, tombol "Salin nomor rekening"
- Dikelola manual, tanpa payment gateway (fase 1-3)

#### 5.1.6 Profil & Pengurus
- Sejarah, visi-misi, struktur organisasi DKM

#### 5.1.7 Galeri
- Galeri foto/video kegiatan, filter per kategori kegiatan

#### 5.1.8 Artikel
- Daftar & detail artikel Islami/info masjid, filter kategori, pencarian judul/kata kunci
- Hanya artikel berstatus "Disetujui" & sudah lewat tanggal publish yang tampil

#### 5.1.9 E-Library (Arsip Materi Kajian)
- Arsip materi kajian yang sudah lewat: PDF slide, rekaman audio/video (embed/link)
- Dikelompokkan per kajian/ustadz/tanggal
- Jamaah bisa unduh/putar materi tanpa perlu hadir langsung

#### 5.1.10 Buku Tamu & Testimoni
- Jamaah bisa menulis kesan/pesan singkat (nama opsional, pesan)
- Testimoni yang dimoderasi (disetujui) tampil di halaman khusus/beranda
- Mencegah spam/konten tidak pantas lewat moderasi sebelum tayang

#### 5.1.11 Kotak Saran & Pengaduan
- Form saran/pengaduan (bisa anonim atau isi kontak), kategori (fasilitas, kegiatan, keuangan, lainnya)
- Jamaah yang mengisi kontak bisa menerima update status tindak lanjut (jika memungkinkan) atau minimal tahu bahwa masukan diterima

#### 5.1.12 Layanan Kurban & Aqiqah
- Form pendaftaran (nama, kontak, jenis hewan, jumlah, catatan)
- Konfirmasi pembayaran tetap manual (transfer + konfirmasi ke bendahara), tanpa payment gateway
- Jamaah menerima nomor pendaftaran sebagai bukti

#### 5.1.13 Layanan Zakat
- Form pendaftaran zakat fitrah/maal (jenis, jumlah jiwa/nominal, kontak)
- Sama seperti kurban, pembayaran & konfirmasi manual

#### 5.1.14 Peminjaman Fasilitas
- Jamaah/pihak eksternal ajukan peminjaman ruang/fasilitas masjid (misal untuk akad nikah, rapat)
- Form: nama fasilitas, tanggal & jam, keperluan, kontak
- Menampilkan kalender ketersediaan (agar tidak mengajukan tanggal yang sudah terpakai)
- Status pengajuan: menunggu → disetujui/ditolak oleh DKM

#### 5.1.15 FAQ
- Daftar pertanyaan umum seputar masjid (jam operasional, cara pinjam fasilitas, cara donasi, dll)
- Dikelompokkan per kategori, bisa dicari

#### 5.1.16 Statistik & Pencapaian
- Counter visual di beranda: total infaq tahun berjalan, jumlah kegiatan terlaksana, jumlah kajian rutin, dll
- Data diambil otomatis dari modul terkait (keuangan, kajian, kegiatan)

#### 5.1.17 Pencarian Global
- Satu kotak pencarian untuk mencari lintas modul: kajian, artikel, pengumuman, FAQ, materi e-library
- Hasil dikelompokkan per jenis konten

#### 5.1.18 Tag Lintas Modul
- Label/tag (misal "Ramadhan", "Anak & Remaja") yang bisa dipasang ke kajian, artikel, dan galeri sekaligus
- Halaman "jelajahi per tag" menampilkan semua konten terkait dari berbagai modul

#### 5.1.19 Kontak & Lokasi
- Alamat: Masjid An-Nur, Tangcity Mall, **Lantai P3a**
- Nomor kontak pengurus, Google Maps embed

### 5.2 Admin Panel (Filament)

**Catatan umum untuk semua modul dengan alur approval** (Pengumuman, Kajian & Kegiatan, Artikel, Peminjaman Fasilitas): setiap record wajib menampilkan riwayat lengkap — siapa yang membuat (created_by), siapa yang meninjau dan mengambil keputusan setuju/tolak (reviewed_by beserta reviewed_at), dan catatan alasan (approval_note) jika ditolak. Ini agar transparan siapa bertanggung jawab atas tiap keputusan, dan mendukung penelusuran lewat Log Aktivitas (5.2.16).

#### 5.2.1 Manajemen Pengumuman
- CRUD pengumuman, alur approval (Sekretaris draft → Ketua DKM setujui/tolak)
- Hanya yang "Disetujui" tampil di running text beranda
- Setiap record menampilkan riwayat: **siapa yang membuat** (created_by), **siapa yang menyetujui/menolak** (reviewed_by) beserta waktunya, dan catatan alasan jika ditolak

#### 5.2.2 Manajemen Kajian & Kegiatan
- CRUD kajian/kegiatan dengan alur approval sama seperti pengumuman
- Kalender view overview seluruh jadwal (termasuk draft, khusus admin)
- **Rekap RSVP**: lihat daftar jamaah yang konfirmasi hadir per kajian/event, total peserta

#### 5.2.3 Manajemen Keuangan
- Input transaksi, kategori sebagai master data, generate ringkasan bulanan otomatis
- Export laporan ke PDF

#### 5.2.4 Manajemen Galeri
- Upload foto/video multi-upload, pengelompokan album/kegiatan

#### 5.2.5 Manajemen Artikel
- CRUD artikel dengan alur approval, manajemen kategori artikel

#### 5.2.6 Manajemen E-Library
- Upload/tautkan materi kajian (PDF, audio, video), kaitkan ke kajian/ustadz terkait

#### 5.2.7 Manajemen Buku Tamu & Testimoni
- Moderasi testimoni masuk: setujui (tampil publik) atau tolak (spam/tidak pantas)

#### 5.2.8 Manajemen Kotak Saran & Pengaduan
- Lihat daftar masukan masuk, ubah status (baru/diproses/selesai), tulis catatan tindak lanjut
- Filter berdasarkan kategori & status

#### 5.2.9 Manajemen Pendaftaran Kurban & Aqiqah
- Lihat & kelola daftar pendaftar, update status pembayaran (manual: belum bayar/lunas)
- Export rekap untuk keperluan panitia kurban

#### 5.2.10 Manajemen Pendaftaran Zakat
- Sama seperti kurban: lihat daftar, update status pembayaran, export rekap

#### 5.2.11 Manajemen Peminjaman Fasilitas
- Kelola daftar fasilitas yang bisa dipinjam
- Lihat pengajuan masuk, approve/reject, otomatis cek bentrok jadwal di kalender

#### 5.2.12 Manajemen FAQ
- CRUD pertanyaan & jawaban, kelompokkan per kategori, atur urutan tampil

#### 5.2.13 Manajemen Pengurus
- CRUD data pengurus (nama, jabatan, foto, periode kepengurusan)

#### 5.2.14 Manajemen Jadwal Sholat
- Konfigurasi lokasi (koordinat) untuk API, override manual, metode hisab
- Pengaturan reminder push notification (aktif/nonaktif per waktu sholat)

#### 5.2.15 Manajemen User & Role
- CRUD user dengan role: Superadmin, Admin (Pengurus Masjid), Ketua DKM, Sekretaris, Bendahara
- Superadmin bisa membuat/mengelola akun Admin dan seluruh role di bawahnya; Admin hanya bisa mengelola role di bawahnya sendiri (Ketua DKM, Sekretaris, Bendahara), tidak bisa membuat sesama Admin/Superadmin
- Permission per role mengikuti matriks di bawah

#### 5.2.16 Log Aktivitas (Audit Log)
- Mencatat aksi penting: create/update/delete, approve/reject, login
- Read-only untuk semua role termasuk Superadmin, filter per user/modul/tanggal

#### 5.2.17 Notifikasi Internal (Admin Panel)
- Ikon lonceng notifikasi di admin panel untuk semua role, menampilkan daftar notifikasi belum dibaca
- **Notifikasi ke Approver** (Ketua DKM/Admin): masuk saat ada Pengumuman, Kajian & Kegiatan, Artikel, atau Peminjaman Fasilitas baru berstatus "Menunggu Approval"
- **Notifikasi ke Pembuat Konten** (Sekretaris): masuk saat draft yang diajukan disetujui atau ditolak — pesan notifikasi menyertakan **nama reviewer** yang mengambil keputusan dan catatan revisi jika ditolak
- **Notifikasi ke Bendahara**: masuk saat ada pendaftaran Kurban/Aqiqah atau Zakat baru yang perlu ditindaklanjuti
- **Notifikasi ke Sekretaris**: masuk saat ada testimoni baru (perlu moderasi) atau kotak saran/pengaduan baru masuk
- Klik notifikasi langsung mengarahkan ke halaman/record terkait
- Fase awal cukup notifikasi in-app (database notification); notifikasi email/WhatsApp menyusul di fase lanjutan (lihat Fase 5)

> **Catatan implementasi — notifikasi dikirim tanpa antrean.**
> `Filament\Notifications\DatabaseNotification` mengimplementasikan `ShouldQueue`,
> sehingga `sendToDatabase()` **selalu** melempar notifikasi ke antrean. Di server
> yang tidak menjalankan `queue:work` — kondisi wajar untuk hosting masjid —
> notifikasi hanya menumpuk di tabel `jobs` dan lonceng pengurus tidak pernah
> berisi, tanpa pesan error sama sekali.
>
> Menyimpan notifikasi hanyalah satu INSERT, jadi mengantrekannya tidak memberi
> keuntungan apa pun sementara risikonya notifikasi tak pernah sampai. Karena itu
> pengirimannya dipaksa langsung lewat `App\Support\ImmediateDatabaseNotification`,
> dan kini tidak bergantung pada nilai `QUEUE_CONNECTION`. Keputusan yang sama
> berlaku untuk Export (lihat 5.2.3): dijalankan `sync`.
>
> **Konsekuensi deploy: instalasi ini tidak membutuhkan queue worker.**

#### 5.2.18 Pengaturan Umum
- Info masjid, rekening & QRIS donasi, logo & identitas visual, daftar fasilitas yang bisa dipinjam

### 5.3 Matriks Hak Akses (Role Permission)

| Modul | Superadmin | Admin (Pengurus Masjid) | Ketua DKM | Sekretaris | Bendahara |
|---|:---:|:---:|:---:|:---:|:---:|
| Pengumuman | CRUD + Approve | CRUD + Approve | R + Approve | CRUD (draft) | R |
| Kajian & Kegiatan | CRUD + Approve | CRUD + Approve | R + Approve | CRUD (draft) | R |
| Keuangan | CRUD | CRUD | R | R | CRUD |
| Galeri | CRUD | CRUD | R | CRUD | R |
| Artikel | CRUD + Approve | CRUD + Approve | R + Approve | CRUD (draft) | R |
| E-Library | CRUD | CRUD | R | CRUD | R |
| Buku Tamu & Testimoni | CRUD + Moderasi | CRUD + Moderasi | R | Moderasi | R |
| Kotak Saran & Pengaduan | CRUD | CRUD | R | CRUD | R |
| Pendaftaran Kurban & Aqiqah | CRUD | CRUD | R | R | CRUD |
| Pendaftaran Zakat | CRUD | CRUD | R | R | CRUD |
| Peminjaman Fasilitas | CRUD + Approve | CRUD + Approve | R + Approve | CRUD | R |
| FAQ | CRUD | CRUD | R | CRUD | - |
| Pengurus | CRUD | CRUD | R | CRUD | - |
| User & Role | CRUD (semua level) | CRUD (di bawah Admin) | - | - | - |
| Log Aktivitas | R (read-only) | R (read-only) | R (read-only) | - | - |
| Pengaturan Umum | CRUD (termasuk teknis) | CRUD (non-teknis) | R | - | - |

*C=Create, R=Read, U=Update, D=Delete, Approve=menyetujui/menolak, Moderasi=setujui/tolak konten dari jamaah. Perbedaan Superadmin vs Admin: Superadmin (developer) punya akses ke pengaturan level sistem/teknis dan bisa membuat akun Admin baru; Admin (pengurus tertinggi masjid) punya akses penuh ke semua modul operasional tapi tidak ke konfigurasi teknis sistem. Daftar nama pemegang tiap role masih perlu dikonfirmasi oleh Tim DKM.*

---

### 5.4 Dashboard Admin

> **Catatan revisi.** Bagian ini ditambahkan saat implementasi. Rancangan awal
> hanya menyebut dashboard admin sekali, di tabel 7.1, sebagai *"statistik/counter
> beranda admin — cukup bawaan `StatsOverviewWidget`, tidak perlu plugin"*.
> Ketentuan itu terpenuhi, tapi setelah dipakai ternyata kurang: papan yang
> dihasilkan memberi tahu **berapa**, tidak pernah **yang mana**. Setiap angka
> jadi jalan buntu — pengurus melihat "menunggu approval: 1" lalu harus menebak
> modulnya dan mencari sendiri di sidebar.

**Prinsip:** dashboard harus membuat tugas tersering selesai tanpa berpindah
halaman. Tim DKM adalah relawan yang membuka sistem sebentar di sela kesibukan,
bukan operator yang duduk seharian di admin panel.

**Susunan widget** — mengikuti urutan pertanyaan yang dibawa pengurus: *"apa yang
perlu saya kerjakan?"* lebih dulu, baru *"bagaimana keadaan masjid?"*.

| # | Widget | Isi | Terlihat oleh |
|---|---|---|---|
| 1 | Perlu Tindakan | Ringkasan sekilas; tiap angka menuju daftarnya | Sesuai wewenang tiap kartu |
| 2 | Antrean Approval | Tabel konten menunggu + tombol Setujui/Tolak di barisnya | Pemegang `approve:*` |
| 3 | Masukan Jamaah Terbaru | Testimoni & kotak saran beserta isinya | Pemegang `moderate:testimonial` / `update:suggestion` |
| 4 | Ringkasan Masjid | Keuangan bulan berjalan, kajian tayang, sholat berikutnya | Semua pengurus |
| 5 | Tren Keuangan | Grafik pemasukan vs pengeluaran 6 bulan | Pemegang `view_any:finance_transaction` |
| 6 | Kesehatan Sistem | Sisa hari jadwal sholat, status pengingat | Pemegang `view_any:prayer_schedule` |

**5.4.1 Dashboard menyesuaikan wewenang pembacanya.** Judul "Perlu Tindakan"
menjanjikan tindakan milik orang yang sedang melihat. Menampilkan antrean
approval kepada Bendahara — yang menurut matriks 5.3 hanya punya hak baca pada
Pengumuman — membuat janji itu tidak ditepati. Tiap widget menentukan sendiri
keterlihatannya lewat `canView()` berdasarkan permission, bukan nama peran.
Ini **bukan** pembatasan keamanan: matriks 5.3 memberi minimal hak baca ke semua
peran pada modul-modul ini. Tujuannya kejujuran tampilan.

Akibatnya papan ini tidak sama bagi semua orang — Bendahara melihat tiga baris
seputar uang, Sekretaris melihat masukan jamaah, Ketua DKM melihat antrean
approval. Hanya Admin dan Superadmin yang memang mengawasi seluruh operasional
yang melihat papan penuh.

**5.4.2 Antrean approval lintas modul.** Pengumuman, kajian, kegiatan, dan
artikel disatukan dalam satu tabel, sehingga Ketua DKM tidak perlu membuka empat
menu terpisah. Keputusan dari sini menempuh jalur yang sama persis dengan
keputusan dari tabel resource — termasuk Log Aktivitas (5.2.16) dan notifikasi
ke pembuat konten (5.2.17).

**5.4.3 Kesehatan sistem — tambahan di luar rancangan awal.** Dua pekerjaan
terjadwal menopang masjid di latar belakang: sinkron jadwal sholat dan pengiriman
pengingat. Keduanya semula tidak punya tempat melapor. Bila cron mati di server,
API jadwal berubah, atau kunci VAPID belum diisi, jadwal di website publik jadi
basi dan pengingat berhenti — dan yang pertama tahu adalah **jamaah yang salah
datang waktu subuh, bukan pengurus**. Tim DKM tidak punya akses SSH untuk
memeriksanya.

Sinyalnya diturunkan dari data yang sudah ditulis sistem saat bekerja normal,
jadi tidak perlu tabel heartbeat baru:

- **Jadwal sholat:** selisih hari antara hari ini dan tanggal terjauh di tabel
  `prayer_schedules`. Menyusut menuju nol berarti sinkronisasi berhenti.
- **Pengingat:** status kunci VAPID, saklar pengingat, jumlah pelanggan, dan
  `last_notified_at` terakhir.

**5.4.4 Kalender sengaja tidak dimasukkan.** Kalender kegiatan punya halamannya
sendiri; menampilkannya lagi di dashboard membuat halaman panjang dan memuat
datanya dua kali. Metrik kunjungan juga tidak ditambahkan — tidak ada anggota
DKM yang akan mengambil tindakan berdasarkan angka itu.

---

### 5.5 Struktur Navigasi Admin

> **Catatan revisi.** Ditambahkan saat implementasi. Rancangan awal mendaftar
> modul (bagian 5.2) tanpa menentukan bagaimana modul-modul itu dikelompokkan di
> menu. Setelah semuanya jadi, hasilnya 25 menu dalam enam kelompok — cukup
> banyak untuk membuat sidebar lebih panjang daripada layar.

**5.5.1 Pengelompokan.** Urutannya mengikuti seberapa sering dipakai Tim DKM:
pekerjaan konten harian di atas, urusan administratif di bawah.

| Urutan | Grup | Isi | Jml |
|---|---|---|---|
| — | (tanpa grup) | Dashboard | 1 |
| 1 | Konten & Informasi | Pengumuman, Kajian, Kegiatan, Kalender Kegiatan | 4 |
| 2 | Media & Pustaka | Artikel, Kategori Artikel, Galeri, E-Library, FAQ | 5 |
| 3 | Layanan Jamaah | Buku Tamu & Testimoni, Kotak Saran, Kurban & Aqiqah, Zakat, Peminjaman Fasilitas, Daftar Fasilitas | 6 |
| 4 | Keuangan | Transaksi, Kategori Keuangan | 2 |
| 5 | Profil Masjid | Pengaturan Umum, Pengurus DKM, Jadwal Sholat | 3 |
| 6 | Sistem | User, Peran & Hak Akses, Log Aktivitas | 3 |

Tiga keputusan yang mengubah susunan awal:

- **"Media & Pustaka" dipecah dari "Konten & Informasi"**, yang semula menampung
  9 menu sementara "Keuangan" hanya 2. Pemisahannya mengikuti sifat isinya:
  konten berbatas waktu (pengumuman, kajian, kegiatan) versus pustaka rujukan
  (artikel, galeri, e-library, FAQ).
- **"Pengaturan Umum" dipindah dari Sistem ke Profil Masjid.** Isinya identitas
  masjid, rekening donasi, dan QRIS — pengurus yang ingin mengganti nomor
  rekening akan mencarinya di Profil, bukan Sistem.
- **Grup "Filament Shield" dihapus.** Nama teknis milik plugin, tidak berarti
  bagi Tim DKM, dan memakan satu baris grup penuh hanya untuk satu menu. Menu
  Peran dipindah ke Sistem dan dilabeli "Peran & Hak Akses".

**5.5.2 Hanya satu grup terbuka (akordion).** Membuka semua grup sekaligus
membuat sidebar jauh melampaui tinggi layar, sehingga pengurus harus menggulir
untuk mencapai menu yang sebenarnya berdekatan. Grup yang memuat halaman sedang
dibuka selalu ikut terbuka — termasuk saat berpindah halaman — agar pengurus
tidak kehilangan jejak posisinya.

**5.5.3 Ikon di tingkat grup, bukan di tiap menu.** Filament hanya mengizinkan
salah satu. Menaruhnya di grup sekaligus mengaktifkan penanda hierarki bawaan
Filament: menu di dalam grup dirender sebagai titik bertali di bawah ikon
grupnya, sehingga keanggotaan tiap menu terbaca sekilas.

---

## 6. Kebutuhan Non-Fungsional

- **Performa:** Halaman publik load < 2 detik (banyak konten statis/cache)
- **Responsif:** Mobile-first, karena mayoritas jamaah akan akses dari HP
- **Keamanan:** Autentikasi admin panel, hashing password, rate-limiting login, proteksi spam pada form publik (testimoni, saran, RSVP, pendaftaran) — misal honeypot/captcha sederhana. Lapisan tambahan hasil audit (header keamanan, Content-Security-Policy, dan lainnya) dicatat di 6.2
- **Ketersediaan:** Uptime tinggi, hosting sederhana (shared/VPS kecil cukup)
- **Kemudahan penggunaan:** Admin panel harus bisa dipakai tanpa training teknis (form-based, bukan kode)
- **Skalabilitas:** Struktur database mendukung penambahan modul di fase berikutnya (misal payment gateway)

### 6.1 Hasil Verifikasi

Dua target di atas sudah diukur, bukan diperkirakan.

**Performa — terpenuhi.** Diukur di lingkungan development dengan data contoh:

| Halaman | Query | Waktu |
|---|---|---|
| Beranda | 24 | 326 ms |
| Kajian | 5 | 33 ms |
| Laporan keuangan | 12 | 48 ms |
| Galeri | 6 | 13 ms |

Jauh di bawah target 2 detik, tanpa gejala N+1. Angka ini akan berbeda di server
produksi, jadi perlu diukur ulang setelah deploy.

**Responsif — terpenuhi setelah perbaikan.** Diuji pada 10 lebar viewport
(280–1440 px, mencakup layar luar Galaxy Fold sampai desktop) di seluruh 18
halaman publik: 180 pengukuran, nol overflow horizontal.

Perbaikan yang diperlukan: pola `lg:grid-cols-[minmax(0,…)]` hanya memasang
`minmax(0,…)` di breakpoint `lg`. Di layar ponsel grid jatuh ke satu kolom
`auto`, dan kolom `auto` tidak boleh menyusut di bawah min-content isinya —
sehingga tabel `min-w-160` mengunci seluruh halaman selebar 640 px meski
tabelnya sendiri sudah dibungkus area gulir. Ditambahkan `grid-cols-1` pada
19 berkas view; `tests/Feature/ResponsifPublikTest.php` menjaganya.

> Yang diukur adalah **overflow horizontal**. Cacat visual lain (elemen
> bertumpuk, teks terpotong) tidak tertangkap metode ini dan masih perlu
> pemeriksaan mata pada perangkat nyata.

### 6.2 Keamanan — Hasil Audit

Poin **Keamanan** di atas hanya menyebut autentikasi, hashing, rate limiting, dan
proteksi spam. Setelah deploy pertama, pemindaian securityheaders.com memberi
nilai **F**, dan audit menyeluruh yang menyusul menambahkan lapisan berikut.
Semuanya improvisasi di luar rancangan awal.

**Header keamanan** — `app/Http/Middleware/SecurityHeaders.php`, dipasang di grup
`web` dan di tumpukan middleware panel Filament (yang tidak memakai grup `web`):

| Header | Nilai | Menutup |
|---|---|---|
| `Strict-Transport-Security` | `max-age=31536000; includeSubDomains`, hanya lewat HTTPS | Penurunan paksa ke HTTP |
| `X-Frame-Options` | `SAMEORIGIN` | Clickjacking lewat iframe |
| `X-Content-Type-Options` | `nosniff` | Berkas unggahan ditafsirkan sebagai skrip |
| `Referrer-Policy` | `strict-origin-when-cross-origin` | Alamat halaman bocor ke situs luar |
| `Permissions-Policy` | kamera, mikrofon, lokasi, pembayaran, USB dimatikan | Penyalahgunaan fitur browser |
| `Content-Security-Policy` | lihat di bawah | Eksekusi skrip sisipan |

**Content-Security-Policy dibelah dua:**

- *Halaman publik — ketat.* `script-src 'self'` tanpa `'unsafe-inline'`. Artikel,
  pengumuman, dan FAQ memakai RichEditor dan dirender sebagai HTML mentah;
  dengan kebijakan ini `<script>` yang disisipkan lewat konten diblokir browser.
  Peta di halaman kontak diizinkan lewat `frame-src` untuk `maps.google.com` dan
  `www.google.com` — yang pertama mengalihkan ke yang kedua.
- *Admin panel — dilonggarkan.* `'unsafe-inline' 'unsafe-eval'`, karena Alpine.js
  dan Livewire membutuhkannya, ditambah `worker-src blob:` untuk pratinjau gambar
  di kolom unggah — FilePond menggambarnya lewat Web Worker dari URL `blob:`.
  Admin berada di balik login, sehingga paparannya jauh lebih kecil.

Konsekuensinya, view publik tidak boleh memuat skrip inline maupun atribut
`on…=`. Dua yang sempat ada — `onchange` pada filter kategori kegiatan dan
`onerror` pada gambar cadangan — dipindah ke `resources/js/app.js`. Tanpa
pemindahan itu, filter kategori tidak akan bereaksi sama sekali di produksi.
`tests/Feature/ContentSecurityPolicyTest.php` menjaga kedua larangan tersebut.

**Sakelar darurat `CSP_REPORT_ONLY`.** Bila `true`, pelanggaran hanya dicatat di
konsol browser, tidak diblokir. Bawaannya `true` di lokal — server Vite
(`npm run dev`) berjalan di origin lain — dan `false` di tempat lain. Bila CSP
ternyata mematahkan sesuatu di produksi, isi `CSP_REPORT_ONLY=true` di
environment server lalu restart, tanpa mengubah kode.

**Temuan lain dari audit yang sama:**

| Temuan | Dampak | Perbaikan |
|---|---|---|
| Deskripsi kegiatan dirender tanpa escape (`{!! nl2br(...) !!}`) | Stored XSS: skrip yang ditulis lewat form admin tereksekusi di halaman publik | `nl2br(e(...))`; `EscapingKontenTest` |
| Aplikasi di belakang proxy (Cloudflare Tunnel) tidak mengenali HTTPS | HSTS tidak terkirim; tautan yang dibangun dari request memakai `http://` | `trustProxies` di `bootstrap/app.php`, env `TRUSTED_PROXIES`; `DiBelakangProxyTest` |
| `tags` tidak ada di `$fillable` Article, Event, GalleryAlbum, Study | Di produksi tag hilang diam-diam; di lokal menyimpan galeri berakhir `MassAssignmentException` | Ditambahkan ke `#[Fillable]`; `TagLintasModulTest` |
| Kolom Role di Manajemen User tanpa eager load | N+1 query di produksi; error 500 di lokal begitu ada lebih dari satu akun | `modifyQueryUsing(... with('roles'))`; `AdminPanelAccessTest` |
| `composer audit` dan `npm audit` | Nol kerentanan diketahui | — |

**Diverifikasi di browser sungguhan**, bukan hanya lewat test: Chromium headless
dengan CSP ditegakkan, 22 halaman publik dan 43 halaman admin (termasuk login,
RichEditor, unggah berkas, kalender, dan grafik dashboard). Kontrol positif:
skrip inline yang disisipkan ke halaman publik terbukti diblokir, sedangkan di
admin tetap berjalan. Pemeriksaan yang sama diulang di produksi (di balik
Cloudflare) setelah deploy: 19 halaman publik dan halaman login admin, nol
pelanggaran; font lokal, peta, dan gambar cadangan berfungsi.

Satu kasus lolos dari pemeriksaan tersebut: pratinjau gambar yang **sudah
tersimpan** di kolom unggah admin. Form *create* yang diperiksa masih kosong,
sehingga FilePond tidak pernah membuat worker-nya; setelah deploy, form ubah
menampilkan kotak abu-abu berisi nama berkas saja. Diperbaiki dengan
`worker-src 'self' blob:`, dibuktikan di browser (pratinjau kembali tergambar,
nol pelanggaran), dan dijaga `ContentSecurityPolicyTest`.

**Belum dikerjakan:** avatar bawaan Filament diambil dari `ui-avatars.com`,
sehingga nama pengurus terkirim ke layanan pihak ketiga. Bisa diganti avatar
lokal bila dianggap perlu.

---

## 7. Tech Stack

| Layer | Teknologi | Alasan |
|---|---|---|
| Backend & Admin Panel | Laravel + Filament | CRUD cepat, role & permission built-in, cocok untuk tim non-teknis |
| Frontend Publik | Blade (server-rendered) | Ringan, SEO-friendly, tidak perlu SPA untuk konten informasi |
| Database | MySQL | Standar, mudah di-hosting shared/VPS |
| Jadwal Sholat | Aladhan API atau API Kemenag | Data akurat, auto-update |
| Push Notification | Web Push API (VAPID) | Reminder sholat tanpa perlu aplikasi native |
| Pencarian Global | Laravel Scout (driver database/meilisearch ringan) | Pencarian lintas modul yang cepat |
| Storage Media | Local storage / S3-compatible (opsional) | Untuk galeri, e-library, foto/video |
| Export PDF | Laravel DomPDF / Spatie PDF | Untuk laporan keuangan & rekap kurban/zakat |

### 7.1 Plugin Filament yang Digunakan

| Kebutuhan | Package | Fungsi |
|---|---|---|
| Role & Permission | `spatie/laravel-permission` + `bezhansalleh/filament-shield` | Manajemen role (Superadmin, Admin, Ketua DKM, Sekretaris, Bendahara) dengan UI permission di Filament, auto-generate policy per resource |
| Log Aktivitas | `spatie/laravel-activitylog` + ~~`z3d0x/filament-logger`~~ → `ActivityLogResource` buatan sendiri | Mencatat & menampilkan audit log (create/update/delete/approve/reject/login) di admin panel |
| Upload media (galeri, e-library, poster) | ~~`spatie/laravel-medialibrary`~~ → `FileUpload` bawaan Filament | Kelola upload foto/video/PDF |
| Rich text editor (artikel, pengumuman) | Bawaan Filament: `Forms\Components\RichEditor` | Cukup pakai built-in; upgrade ke `awcodes/filament-tiptap-editor` hanya jika perlu fitur lebih advanced |
| Kalender (kajian, kegiatan, cek bentrok peminjaman fasilitas) | `saade/filament-fullcalendar` | Tampilan kalender interaktif untuk overview jadwal & deteksi bentrok booking |
| Notifikasi internal (in-app) | Bawaan Filament: `Filament\Notifications` (database notifications) | Sudah termasuk di Filament core untuk lonceng notifikasi approval |
| Export laporan (keuangan, rekap kurban/zakat) | ~~`pxlrbt/filament-excel`~~ → `Filament\Actions\Exports` bawaan | Export CSV/XLSX langsung dari tabel Filament |
| Tag lintas modul | `spatie/laravel-tags` (tanpa plugin Filament resmi, custom field/relation manager) | Data tag polymorphic untuk kajian/artikel/galeri |
| Dashboard widget (statistik/counter beranda admin) | Bawaan Filament: `StatsOverviewWidget`, `TableWidget`, `ChartWidget` | Cukup built-in, tidak perlu plugin. Susunan lengkapnya di 5.4 |
| Profile akun user (admin panel) | ~~`jeffgreco13/filament-breezy`~~ → `->profile()` bawaan panel | Halaman edit profil, ganti password, avatar per user yang login |
| Web Push (reminder sholat) | ~~`minishlink/web-push`~~ → `WebPushService` buatan sendiri | VAPID (ES256) diimplementasikan langsung |

*Prinsip: pakai fitur built-in Filament dulu (rich editor, notifikasi, widget stats) sebelum menambah plugin, agar dependency tetap ringan. Push notification (reminder sholat) berjalan di sisi frontend publik (Web Push API), bukan plugin Filament.*

> **Catatan revisi — lima paket batal dipakai.** Baris bercoret di atas adalah
> rencana awal yang tidak jadi dipasang saat implementasi. Alasannya berbeda-beda,
> dan semuanya diputuskan saat pengerjaan:
>
> | Paket | Alasan batal | Penggantinya |
> |---|---|---|
> | `z3d0x/filament-logger` | Hanya mendukung Filament 3, proyek ini Filament 5 | `ActivityLogResource` buatan sendiri, lengkap dengan penerjemah `ActivityLogPresenter` |
> | `minishlink/web-push` | Menuntut Guzzle 7, bentrok dengan dependency proyek | `WebPushService`: JWT ES256 + konversi tanda tangan DER→raw |
> | `spatie/laravel-medialibrary` | Berlebihan untuk kebutuhan yang ada | `FileUpload` bawaan Filament |
> | `pxlrbt/filament-excel` | Filament 5 sudah punya Exporter sendiri | `Filament\Actions\Exports` |
> | `jeffgreco13/filament-breezy` | Filament 5 sudah punya halaman profil | `->profile()` pada panel |
>
> Tiga yang terakhir mengikuti prinsip "built-in dulu" yang ditulis PRD ini
> sendiri. Dua yang pertama terpaksa, karena paketnya tidak kompatibel.
>
> Konsekuensi yang perlu diketahui saat deploy: tidak ada dependency tambahan
> untuk lima kebutuhan itu, jadi `composer.json` lebih ramping daripada yang
> dibayangkan rancangan awal.

---

## 8. Rancangan Struktur Database (Skema Utama)

```
users
- id, name, email, password, role_id, created_at, updated_at

roles
- id, name (superadmin, admin, ketua_dkm, sekretaris, bendahara), level (angka urutan hierarki untuk validasi akses)

mosque_settings
- id, name, address (default: "Lantai P3a, Tangcity Mall"), phone, description,
  bank_account_name, bank_account_number, qris_image, logo, updated_at

prayer_schedules
- id, date, fajr, dhuhr, asr, maghrib, isha, is_override, created_by

push_subscriptions
- id, endpoint, keys (json), user_agent, created_at

announcements
- id, title, content, start_date, end_date, priority,
  status (draft/menunggu_approval/disetujui/ditolak),
  approval_note, created_by, reviewed_by, reviewed_at

studies (kajian)
- id, ustadz_name, theme, schedule_type (rutin/insidental),
  day_of_week (nullable), time, location, description,
  status (draft/menunggu_approval/disetujui/ditolak),
  approval_note, created_by, reviewed_by, reviewed_at

events (kegiatan)
- id, title, description, poster_image, event_date, category,
  status (draft/menunggu_approval/disetujui/ditolak),
  approval_note, created_by, reviewed_by, reviewed_at

rsvps
- id, rsvpable_id, rsvpable_type (polymorphic: study/event), name, phone,
  jumlah_orang, created_at

finance_categories
- id, name, type (in/out), description

finance_transactions
- id, date, type (in/out), finance_category_id, amount, description, created_by

finance_monthly_summary (view/generated)
- month, year, finance_category_id (nullable), total_in, total_out, balance

article_categories
- id, name, slug

articles
- id, title, slug, content, cover_image, article_category_id, publish_date,
  status (draft/menunggu_approval/disetujui/ditolak),
  approval_note, created_by, reviewed_by, reviewed_at

library_materials (e-library)
- id, title, type (pdf/audio/video/slide), file_path_or_url, study_id (nullable),
  description, created_by, created_at

gallery_albums
- id, title, category, event_date

gallery_items
- id, album_id, type (image/video), file_path, caption

testimonials
- id, name (nullable), message, status (menunggu/disetujui/ditolak), created_at

suggestions (kotak saran & pengaduan)
- id, name (nullable), contact (nullable), category, message,
  status (baru/diproses/selesai), response_note, handled_by, created_at

qurban_registrations
- id, name, phone, animal_type, quantity, payment_status (belum_bayar/lunas),
  notes, created_at

zakat_registrations
- id, name, phone, zakat_type (fitrah/maal), amount_or_jiwa,
  payment_status (belum_bayar/lunas), created_at

facilities
- id, name, description, capacity

facility_bookings
- id, facility_id, name, phone, purpose, booking_date, start_time, end_time,
  status (menunggu/disetujui/ditolak), reviewed_by, reviewed_at, created_at

faqs
- id, question, answer, category, sort_order

tags
- id, name, slug

taggables (polymorphic pivot)
- id, tag_id, taggable_id, taggable_type

board_members (pengurus)
- id, name, position, photo, period_start, period_end

activity_logs
- id, user_id, action (create/update/delete/approve/reject/login), module,
  record_id (nullable), description, ip_address, created_at

notifications
- id, user_id (penerima), type (approval_request/approval_result/new_suggestion/
  new_testimonial/new_registration/dll), title, message, link (url ke record terkait),
  is_read, created_at
```

### 8.1 Data Dummy User Awal (Seeder)

Untuk keperluan development & testing sebelum data pengurus asli tersedia:

| Nama | Email | Role | Keterangan |
|---|---|---|---|
| Iqbal (Superadmin) | superadmin@masjidannur.test | Superadmin | Akses penuh, akun developer/pengelola teknis sistem |
| Admin Masjid (dummy) | admin@masjidannur.test | Admin (Pengurus Masjid) | Akses penuh ke semua modul operasional |
| Ahmad Fauzi (dummy) | ketua@masjidannur.test | Ketua DKM | Approver pengumuman, kajian, artikel, fasilitas |
| Budi Santoso (dummy) | sekretaris@masjidannur.test | Sekretaris | Membuat draft konten, moderasi testimoni |
| Citra Dewi (dummy) | bendahara@masjidannur.test | Bendahara | Input transaksi keuangan, kelola kurban/zakat |

*Password default seeder: disarankan random per environment (jangan hardcode di kode produksi), atau pakai `password` khusus untuk environment local/testing saja. Data dummy ini wajib diganti dengan data pengurus asli sebelum go-live.*

---

## 9. User Flow Utama

**Jamaah mengecek jadwal sholat & kajian:**
Buka website → Beranda menampilkan jadwal hari ini → klik "Jadwal Lengkap" untuk bulanan → klik "Kajian" untuk lihat jadwal pengajian → opsional isi RSVP jika ingin hadir.

**Bendahara input laporan keuangan:**
Login admin panel → Menu Keuangan → Tambah Transaksi → isi tanggal, kategori, jenis, nominal → sistem otomatis update ringkasan bulanan → publik bisa lihat & filter di halaman Laporan Keuangan.

**Sekretaris membuat pengumuman (dengan approval):**
Login admin panel → Menu Pengumuman → Tambah → isi judul, isi, tanggal tayang → submit sebagai "Menunggu Approval" → Ketua DKM login → tinjau → Setujui (otomatis tayang) atau Tolak (kembali ke Sekretaris beserta catatan revisi).

**Jamaah mendaftar kurban:**
Buka halaman Layanan Kurban → isi form (nama, kontak, jenis hewan, jumlah) → submit → dapat nomor pendaftaran → transfer manual ke rekening panitia → konfirmasi ke Bendahara → Bendahara update status "Lunas" di admin panel.

**Jamaah mengajukan peminjaman fasilitas:**
Buka halaman Peminjaman Fasilitas → pilih fasilitas & lihat kalender ketersediaan → isi form tanggal/jam/keperluan → submit → status "Menunggu" → Ketua DKM/Sekretaris review → Setujui/Tolak → jamaah bisa cek status di halaman konfirmasi (via nomor pengajuan).

---

## 10. Rencana Fase Pengembangan

### Fase 1 — MVP (Prioritas Utama)
- Setup Laravel + Filament, auth & role
- Modul: Pengaturan umum, Jadwal sholat (API), Pengumuman, Kajian, Log Aktivitas, FAQ
- **Notifikasi internal (in-app)** untuk alur approval Pengumuman & Kajian — dibangun bareng modul approval, bukan menyusul belakangan
- Website publik: Beranda, Jadwal Sholat, Kajian, FAQ, Kontak
- **Dokumentasi instalasi awal (lokal)**: langkah setup environment dev (clone, composer install, migration, seeding) — ditulis paralel sambil setup, bukan di akhir

### Fase 2
- Modul Keuangan (input transaksi + laporan publik filterable)
- Modul Galeri, Artikel, E-Library
- Modul Buku Tamu & Testimoni (dengan moderasi)
- Halaman Donasi (rekening & QRIS)
- Perluasan notifikasi internal: approval Artikel, notifikasi testimoni baru masuk ke Sekretaris

### Fase 3
- Modul Pengurus & profil lengkap, kalender kegiatan tahunan, export PDF laporan keuangan
- Modul Kotak Saran & Pengaduan
- Modul RSVP Kajian
- Modul Peminjaman Fasilitas
- Statistik & Pencapaian (counter beranda)
- Pencarian Global & Tag lintas modul
- Perluasan notifikasi internal: approval Peminjaman Fasilitas, kotak saran/pengaduan baru masuk ke Sekretaris

### Fase 4
- Modul Pendaftaran Kurban & Aqiqah (manual payment)
- Modul Pendaftaran Zakat (manual payment)
- Reminder push notification jadwal sholat
- Perluasan notifikasi internal: pendaftaran kurban/zakat baru masuk ke Bendahara
- **Dokumentasi instalasi production**: langkah deploy ke server (requirement server, env production, queue/cron untuk jadwal sholat & push notification, backup), ditulis sebagai panduan "deploy ulang untuk masjid lain"

### Fase 5 (Opsional, di luar scope awal)
- Integrasi payment gateway untuk donasi, kurban, zakat, sewa fasilitas
- Notifikasi WhatsApp/email otomatis
- Aplikasi mobile native

---

## 11. Metrik Keberhasilan

- Tim DKM bisa update pengumuman/kajian sendiri tanpa bantuan developer dalam waktu < 5 menit
- Laporan keuangan bulanan konsisten ter-update tiap awal bulan
- Jamaah bisa menemukan jadwal sholat hari itu dalam < 3 klik dari beranda
- Ada partisipasi jamaah lewat testimoni/saran/RSVP dalam 1 bulan pertama peluncuran
- Tidak ada downtime signifikan selama periode ramai (Ramadhan, hari besar, musim kurban)

---

## 12. Risiko & Mitigasi

| Risiko | Mitigasi |
|---|---|
| Tim DKM tidak konsisten update data | Buat panduan penggunaan admin panel (screenshot/video singkat) |
| API jadwal sholat down/berubah | Sediakan fallback input manual oleh admin |
| Kesalahan input keuangan | Tambahkan konfirmasi sebelum submit, riwayat log perubahan |
| Data hilang (server issue) | Backup database rutin (harian/mingguan) |
| Spam pada form publik (testimoni, saran, RSVP, pendaftaran) | Tambahkan honeypot/captcha sederhana, moderasi sebelum tayang publik |
| Bentrok jadwal peminjaman fasilitas | Validasi otomatis di form berdasarkan kalender ketersediaan |
| Cakupan modul terlalu banyak untuk solo dev | Kembangkan bertahap sesuai fase, prioritaskan modul dengan dampak tertinggi lebih dulu |

---

## 13. Keputusan & Pertanyaan Terbuka

**Sudah diputuskan:**
1. Kategori laporan keuangan bersifat fleksibel (master data, semua jenis) dan wajib bisa difilter multi-dimensi oleh jamaah.
2. Pengumuman, kajian/kegiatan, dan artikel wajib melalui alur approval (Sekretaris membuat draft → Ketua DKM menyetujui) sebelum tayang publik.
3. Lokasi masjid cukup dituliskan sebagai "Lantai P3a, Tangcity Mall" tanpa detail petunjuk arah internal.
4. Daftar user admin awal menggunakan data dummy (akan diganti data pengurus asli belakangan), minimal ada 1 akun superadmin — lihat tabel dummy user di bagian 8.1.
5. Seluruh modul tambahan (buku tamu, RSVP, kotak saran, kurban, zakat, peminjaman fasilitas, e-library, FAQ, statistik, pencarian global, tag, push notification) disetujui untuk masuk scope, dikembangkan bertahap sesuai fase di bagian 10.
6. Sistem dirancang **reusable** (bukan multi-tenant) — tiap masjid yang memakai sistem ini punya instalasi/database terpisah, di-deploy ulang dari codebase yang sama.
7. Ditambahkan role **Admin (Pengurus Masjid)** di atas Ketua DKM: Superadmin (Iqbal, developer) punya akses penuh termasuk pengaturan teknis sistem; Admin punya akses penuh ke seluruh modul operasional masjid tapi tanpa akses konfigurasi level sistem.
8. Dokumentasi instalasi (lokal untuk development di Fase 1, dan production/deploy di Fase 4) menjadi deliverable wajib, ditulis paralel selama pengembangan agar mendukung tujuan reusable codebase.
9. Ditambahkan modul **Notifikasi Internal (in-app)** untuk semua alur approval dan interaksi jamaah (moderasi testimoni, kotak saran, pendaftaran kurban/zakat, peminjaman fasilitas) — dibangun bertahap mengikuti fase modul terkait, dimulai dari Fase 1. Notifikasi email/WhatsApp otomatis tetap di Fase 5 (di luar scope awal).

---

*Dokumen ini adalah draft awal dan dapat direvisi berdasarkan masukan Tim DKM sebelum masuk fase development.*
