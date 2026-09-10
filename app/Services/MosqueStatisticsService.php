<?php

namespace App\Services;

use App\Enums\ContentStatus;
use App\Enums\TransactionType;
use App\Models\Event;
use App\Models\FinanceTransaction;
use App\Models\LibraryMaterial;
use App\Models\Study;
use Illuminate\Support\Facades\Cache;

/**
 * Counter pencapaian untuk beranda (PRD 5.1.16). Angkanya dihitung otomatis
 * dari modul terkait dan di-cache singkat agar beranda tetap ringan.
 */
class MosqueStatisticsService
{
    private const CACHE_KEY = 'masjid.statistik.beranda';

    private const CACHE_TTL_MINUTES = 15;

    /**
     * @return list<array{label: string, value: string, caption: string}>
     */
    public function highlights(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(self::CACHE_TTL_MINUTES), function (): array {
            $year = today()->year;

            $infaq = (float) FinanceTransaction::query()
                ->whereYear('date', $year)
                ->where('type', TransactionType::In)
                ->sum('amount');

            return [
                [
                    'label' => 'Infaq & donasi tahun ini',
                    'value' => 'Rp '.number_format($infaq, 0, ',', '.'),
                    'caption' => 'Terhimpun sepanjang '.$year,
                ],
                [
                    'label' => 'Kajian rutin',
                    'value' => (string) Study::query()->where('status', ContentStatus::Disetujui)->count(),
                    'caption' => 'Majelis ilmu yang berjalan',
                ],
                [
                    'label' => 'Kegiatan terlaksana',
                    'value' => (string) Event::query()
                        ->where('status', ContentStatus::Disetujui)
                        ->whereDate('event_date', '<', today())
                        ->count(),
                    'caption' => 'Sejak masjid berdiri',
                ],
                [
                    'label' => 'Materi e-library',
                    'value' => (string) LibraryMaterial::query()->count(),
                    'caption' => 'Slide, audio, dan video kajian',
                ],
            ];
        });
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
