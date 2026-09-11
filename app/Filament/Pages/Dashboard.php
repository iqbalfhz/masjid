<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ActionCenterWidget;
use App\Filament\Widgets\ApprovalQueueWidget;
use App\Filament\Widgets\FinanceTrendWidget;
use App\Filament\Widgets\JamaahInputWidget;
use App\Filament\Widgets\MosqueOverviewWidget;
use App\Filament\Widgets\SystemHealthWidget;
use Filament\Pages\Dashboard as BaseDashboard;

/**
 * Dashboard pengurus.
 *
 * Urutannya mengikuti pertanyaan yang dibawa pengurus saat membuka sistem:
 * "apa yang perlu saya kerjakan?" lebih dulu, baru "bagaimana keadaan masjid?".
 *
 * 1. Perlu Tindakan     — ringkasan sekilas, tiap angka menuju daftarnya
 * 2. Antrean Approval   — tempat keputusan benar-benar diambil
 * 3. Masukan Jamaah     — testimoni & saran yang perlu dibaca, bukan dihitung
 * 4. Ringkasan Masjid   — keuangan, kajian, waktu sholat berikutnya
 * 5. Tren Keuangan      — arah enam bulan, konteks bagi angka bulan berjalan
 * 6. Kesehatan Sistem   — apakah pekerjaan terjadwal masih hidup
 *
 * Tiap widget menentukan sendiri siapa yang boleh melihatnya lewat canView(),
 * mengikuti matriks hak akses PRD 5.3. Jadi papan ini tidak sama bagi semua
 * orang: Bendahara melihat tiga baris seputar uang, Sekretaris melihat masukan
 * jamaah, dan Ketua DKM melihat antrean approval. Hanya Admin dan Superadmin
 * yang memang mengawasi semuanya melihat papan penuh.
 *
 * Widget dipilih eksplisit, bukan seluruh widget yang ditemukan panel: kalender
 * kegiatan punya halamannya sendiri, dan menampilkannya lagi di sini membuat
 * dashboard panjang sekaligus memuat datanya dua kali.
 */
class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard';

    protected static ?string $navigationLabel = 'Dashboard';

    /**
     * @return array<class-string>
     */
    public function getWidgets(): array
    {
        return [
            ActionCenterWidget::class,
            ApprovalQueueWidget::class,
            JamaahInputWidget::class,
            MosqueOverviewWidget::class,
            FinanceTrendWidget::class,
            SystemHealthWidget::class,
        ];
    }

    /**
     * @return int|array<string, ?int>
     */
    public function getColumns(): int|array
    {
        return 4;
    }
}
