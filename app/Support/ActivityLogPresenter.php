<?php

namespace App\Support;

use App\Models\Study;
use App\Models\User;
use BackedEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use Throwable;

/**
 * Menerjemahkan isi Log Aktivitas menjadi bahasa yang dimengerti pengurus
 * (PRD 5.2.16). Tanpa ini, log menampilkan nama tabel dan nama kolom mentah
 * seperti "article" atau "views" yang tidak berarti bagi Tim DKM.
 */
class ActivityLogPresenter
{
    /**
     * Nama modul dalam bahasa Indonesia, dikunci dengan nama model snake_case.
     *
     * @var array<string, string>
     */
    public const MODULES = [
        'announcement' => 'Pengumuman',
        'study' => 'Kajian',
        'event' => 'Kegiatan',
        'rsvp' => 'RSVP',
        'article' => 'Artikel',
        'article_category' => 'Kategori Artikel',
        'finance_transaction' => 'Transaksi Keuangan',
        'finance_category' => 'Kategori Keuangan',
        'gallery_album' => 'Album Galeri',
        'gallery_item' => 'Isi Galeri',
        'library_material' => 'Materi E-Library',
        'testimonial' => 'Testimoni',
        'suggestion' => 'Kotak Saran',
        'qurban_registration' => 'Pendaftaran Kurban',
        'zakat_registration' => 'Pendaftaran Zakat',
        'facility' => 'Fasilitas',
        'facility_booking' => 'Peminjaman Fasilitas',
        'faq' => 'FAQ',
        'board_member' => 'Pengurus',
        'prayer_schedule' => 'Jadwal Sholat',
        'mosque_setting' => 'Pengaturan Umum',
        'user' => 'Akun Pengguna',
    ];

    /**
     * Aksi beserta warna badge-nya.
     *
     * @var array<string, array{label: string, color: string}>
     */
    public const EVENTS = [
        'created' => ['label' => 'Ditambahkan', 'color' => 'success'],
        'updated' => ['label' => 'Diubah', 'color' => 'info'],
        'deleted' => ['label' => 'Dihapus', 'color' => 'danger'],
        'submitted' => ['label' => 'Diajukan', 'color' => 'warning'],
        'approved' => ['label' => 'Disetujui', 'color' => 'success'],
        'rejected' => ['label' => 'Ditolak', 'color' => 'danger'],
        'login' => ['label' => 'Masuk', 'color' => 'gray'],
        'logout' => ['label' => 'Keluar', 'color' => 'gray'],
    ];

    /**
     * Kolom teknis yang tidak perlu dilihat pengurus.
     *
     * @var list<string>
     */
    public const HIDDEN_ATTRIBUTES = [
        'slug', 'password', 'remember_token', 'created_at', 'updated_at',
        'email_verified_at', 'endpoint_hash', 'ip_address',
    ];

    /**
     * Nama kolom dalam bahasa Indonesia.
     *
     * @var array<string, string>
     */
    public const ATTRIBUTES = [
        'title' => 'Judul',
        'theme' => 'Tema kajian',
        'name' => 'Nama',
        'email' => 'Email',
        'phone' => 'Nomor kontak',
        'contact' => 'Kontak',
        'content' => 'Isi',
        'excerpt' => 'Ringkasan',
        'description' => 'Deskripsi',
        'message' => 'Pesan',
        'question' => 'Pertanyaan',
        'answer' => 'Jawaban',
        'status' => 'Status',
        'priority' => 'Prioritas',
        'category' => 'Kategori',
        'type' => 'Jenis',
        'position' => 'Jabatan',
        'location' => 'Lokasi',
        'purpose' => 'Keperluan',
        'ustadz_name' => 'Nama ustadz',
        'schedule_type' => 'Jenis jadwal',
        'day_of_week' => 'Hari',
        'time' => 'Jam mulai',
        'start_time' => 'Jam mulai',
        'end_time' => 'Jam selesai',
        'date' => 'Tanggal',
        'start_date' => 'Tanggal mulai',
        'end_date' => 'Tanggal berakhir',
        'event_date' => 'Tanggal kegiatan',
        'publish_date' => 'Tanggal tayang',
        'booking_date' => 'Tanggal pemakaian',
        'material_date' => 'Tanggal materi',
        'period_start' => 'Periode mulai',
        'period_end' => 'Periode selesai',
        'amount' => 'Nominal',
        'quantity' => 'Jumlah',
        'soul_count' => 'Jumlah jiwa',
        'capacity' => 'Kapasitas',
        'person_count' => 'Jumlah orang',
        'reference_no' => 'Nomor bukti',
        'registration_number' => 'Nomor pendaftaran',
        'booking_number' => 'Nomor pengajuan',
        'ticket_code' => 'Nomor tiket',
        'animal_type' => 'Jenis hewan',
        'service_type' => 'Jenis layanan',
        'zakat_type' => 'Jenis zakat',
        'payment_status' => 'Status pembayaran',
        'views' => 'Jumlah dibaca',
        'downloads' => 'Jumlah diakses',
        'sort_order' => 'Urutan tampil',
        'is_active' => 'Aktif',
        'is_published' => 'Tayang di website',
        'is_override' => 'Dikunci dari sinkronisasi',
        'rsvp_enabled' => 'RSVP dibuka',
        'rsvp_quota' => 'Kuota RSVP',
        'approval_note' => 'Catatan reviewer',
        'response_note' => 'Catatan tindak lanjut',
        'notes' => 'Catatan',
        'note' => 'Catatan',
        'created_by' => 'Dibuat oleh',
        'reviewed_by' => 'Ditinjau oleh',
        'moderated_by' => 'Dimoderasi oleh',
        'handled_by' => 'Ditangani oleh',
        'confirmed_by' => 'Dikonfirmasi oleh',
        'reviewed_at' => 'Waktu peninjauan',
        'moderated_at' => 'Waktu moderasi',
        'handled_at' => 'Waktu penanganan',
        'confirmed_at' => 'Waktu konfirmasi',
        'cover_image' => 'Gambar sampul',
        'poster_image' => 'Poster',
        'photo' => 'Foto',
        'logo' => 'Logo',
        'qris_image' => 'Gambar QRIS',
        'file_path' => 'Berkas',
        'external_url' => 'Tautan eksternal',
        'imsak' => 'Imsak',
        'fajr' => 'Subuh',
        'sunrise' => 'Syuruq',
        'dhuhr' => 'Dzuhur',
        'asr' => 'Ashar',
        'maghrib' => 'Maghrib',
        'isha' => 'Isya',
    ];

    public function moduleLabel(Activity $activity): string
    {
        return self::MODULES[$this->moduleKey($activity)]
            ?? Str::of($this->moduleKey($activity))->replace('_', ' ')->title()->value();
    }

    /**
     * Kunci modul yang konsisten, baik untuk baris lama (log_name berspasi)
     * maupun baris baru.
     */
    public function moduleKey(Activity $activity): string
    {
        if ($activity->subject_type !== null) {
            return Str::snake(class_basename($activity->subject_type));
        }

        return Str::snake(str_replace(' ', '_', (string) $activity->log_name));
    }

    public function eventLabel(?string $event): string
    {
        return self::EVENTS[$event]['label'] ?? Str::headline((string) $event);
    }

    public function eventColor(?string $event): string
    {
        return self::EVENTS[$event]['color'] ?? 'gray';
    }

    public function causerName(Activity $activity): string
    {
        return $activity->causer?->name ?? 'Sistem';
    }

    /**
     * Nama record yang bisa dikenali pengurus, misal judul artikel — bukan
     * "Article #4".
     */
    public function recordLabel(Activity $activity): string
    {
        $subject = $activity->subject;

        if ($subject === null) {
            return $activity->subject_id === null
                ? '—'
                : $this->moduleLabel($activity).' yang sudah dihapus';
        }

        foreach (['title', 'theme', 'question', 'name', 'registration_number', 'booking_number', 'ticket_code'] as $key) {
            if (filled($subject->getAttribute($key))) {
                return Str::limit((string) $subject->getAttribute($key), 60);
            }
        }

        if (filled($subject->getAttribute('date'))) {
            return $this->moduleLabel($activity).' '.$this->formatValue($subject, 'date', $subject->getAttribute('date'));
        }

        return $this->moduleLabel($activity).' #'.$activity->subject_id;
    }

    /**
     * Ringkasan singkat untuk kolom tabel.
     *
     * Perbandingan "lama → baru" hanya masuk akal pada perubahan. Untuk data
     * yang baru ditambahkan atau dihapus, cukup sebutkan berapa isian yang
     * tercatat daripada menampilkan deretan "(kosong)".
     */
    public function changeSummary(Activity $activity): string
    {
        $changes = $this->changes($activity);

        if ($changes === []) {
            return '—';
        }

        if (! $this->isComparison($activity)) {
            $kata = $activity->event === 'deleted' ? 'isian ikut terhapus' : 'isian tercatat';

            return count($changes).' '.$kata;
        }

        $first = $changes[0];
        $summary = $first['kolom'].': '.$first['sebelum'].' → '.$first['sesudah'];
        $sisa = count($changes) - 1;

        return $sisa > 0
            ? Str::limit($summary, 50).' (+'.$sisa.' kolom lain)'
            : Str::limit($summary, 70);
    }

    /**
     * Benar hanya bila catatan ini memuat nilai lama sekaligus nilai baru,
     * yaitu pada aksi "diubah".
     */
    public function isComparison(Activity $activity): bool
    {
        return filled($activity->attribute_changes?->get('old'));
    }

    /**
     * Judul blok rincian, menyesuaikan jenis aksinya.
     */
    public function changesHeading(Activity $activity): string
    {
        return match (true) {
            $this->isComparison($activity) => 'Rincian Perubahan',
            $activity->event === 'deleted' => 'Data yang Dihapus',
            default => 'Isian yang Tercatat',
        };
    }

    /**
     * Daftar perubahan siap tampil: nama kolom dan nilainya sudah diterjemahkan.
     *
     * @return list<array{kolom: string, sebelum: string, sesudah: string}>
     */
    public function changes(Activity $activity): array
    {
        $perubahan = $activity->attribute_changes?->toArray() ?? [];
        $sebelum = $perubahan['old'] ?? [];
        $sesudah = $perubahan['attributes'] ?? [];

        $subject = $this->subjectInstance($activity);
        $perbandingan = $this->isComparison($activity);
        $baris = [];

        foreach (array_keys($sesudah + $sebelum) as $key) {
            if (in_array($key, self::HIDDEN_ATTRIBUTES, true)) {
                continue;
            }

            $baris[] = [
                'kolom' => $this->attributeLabel($key),
                // Pada data baru/terhapus tidak ada nilai pembanding, jadi
                // ditandai "—" alih-alih "(kosong)" yang menyesatkan.
                'sebelum' => $perbandingan
                    ? $this->formatValue($subject, $key, $sebelum[$key] ?? null)
                    : '—',
                'sesudah' => $this->formatValue($subject, $key, $sesudah[$key] ?? $sebelum[$key] ?? null),
            ];
        }

        return $baris;
    }

    public function attributeLabel(string $key): string
    {
        return self::ATTRIBUTES[$key] ?? Str::of($key)->replace('_', ' ')->ucfirst()->value();
    }

    /**
     * Ubah nilai mentah menjadi teks yang bisa dibaca, memanfaatkan definisi
     * cast pada modelnya sendiri agar enum, tanggal, dan boolean ikut benar.
     */
    public function formatValue(?Model $subject, string $key, mixed $value): string
    {
        if ($value === null || $value === '') {
            return '(kosong)';
        }

        if (str_ends_with($key, '_by')) {
            return User::query()->find($value)?->name ?? 'Pengguna #'.$value;
        }

        if ($key === 'day_of_week') {
            return Study::DAYS[(int) $value] ?? (string) $value;
        }

        $cast = $subject?->getCasts()[$key] ?? null;

        if (is_string($cast) && enum_exists($cast)) {
            $enum = $cast::tryFrom($value);

            if ($enum instanceof BackedEnum) {
                return method_exists($enum, 'getLabel') ? $enum->getLabel() : $enum->value;
            }
        }

        if ($cast === 'boolean') {
            return $value ? 'Ya' : 'Tidak';
        }

        if (is_string($cast) && str_starts_with($cast, 'decimal:')) {
            return 'Rp '.number_format((float) $value, 0, ',', '.');
        }

        if (in_array($cast, ['date', 'datetime'], true)) {
            try {
                return Carbon::parse($value)->translatedFormat($cast === 'date' ? 'd F Y' : 'd F Y, H:i');
            } catch (Throwable) {
                return (string) $value;
            }
        }

        if (is_bool($value)) {
            return $value ? 'Ya' : 'Tidak';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE) ?: '(data)';
        }

        return Str::limit(strip_tags((string) $value), 120);
    }

    /**
     * Instance kosong dari model subjek, dipakai hanya untuk membaca cast-nya.
     */
    private function subjectInstance(Activity $activity): ?Model
    {
        $class = $activity->subject_type;

        if (! is_string($class) || ! class_exists($class)) {
            return null;
        }

        $model = new $class;

        return $model instanceof Model ? $model : null;
    }
}
