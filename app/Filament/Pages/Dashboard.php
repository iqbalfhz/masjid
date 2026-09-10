<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ApprovalQueueWidget;
use App\Filament\Widgets\MosqueOverviewWidget;
use Filament\Pages\Dashboard as BaseDashboard;

/**
 * Dashboard pengurus.
 *
 * Widget dipilih eksplisit, bukan mengambil seluruh widget yang ditemukan
 * panel. Dua alasannya:
 *
 * 1. Kalender kegiatan punya halamannya sendiri; menampilkannya lagi di sini
 *    membuat dashboard panjang dan datanya termuat dua kali.
 * 2. Widget bawaan Filament (info versi framework) tidak berguna bagi Tim DKM.
 */
class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Dasbor';

    protected static ?string $navigationLabel = 'Dasbor';

    /**
     * @return array<class-string>
     */
    public function getWidgets(): array
    {
        return [
            ApprovalQueueWidget::class,
            MosqueOverviewWidget::class,
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
