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
 *
 * Tiap entri membawa nilai mentah (`value`) untuk animasi hitung-naik di sisi
 * browser, sekaligus `display` yang sudah terformat sebagai tampilan awal —
 * jadi angkanya tetap benar meski JavaScript tidak jalan.
 *
 * @phpstan-type Highlight array{label: string, value: float|int, prefix: string, suffix: string, display: string, caption: string, icon: string}
 */
class MosqueStatisticsService
{
    private const CACHE_KEY = 'masjid.statistik.beranda';

    private const CACHE_TTL_MINUTES = 15;

    /**
     * @return list<Highlight>
     */
    public function highlights(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(self::CACHE_TTL_MINUTES), function (): array {
            $year = today()->year;

            $infaq = (float) FinanceTransaction::query()
                ->whereYear('date', $year)
                ->where('type', TransactionType::In)
                ->sum('amount');

            $kajian = Study::query()->where('status', ContentStatus::Disetujui)->count();

            $kegiatan = Event::query()
                ->where('status', ContentStatus::Disetujui)
                ->whereDate('event_date', '<', today())
                ->count();

            $materi = LibraryMaterial::query()->count();

            return [
                $this->entry(
                    label: 'Infaq & donasi tahun ini',
                    value: $infaq,
                    caption: 'Terhimpun sepanjang '.$year,
                    icon: 'banknotes',
                    prefix: 'Rp ',
                ),
                $this->entry(
                    label: 'Kajian rutin',
                    value: $kajian,
                    caption: 'Majelis ilmu yang berjalan',
                    icon: 'book',
                ),
                $this->entry(
                    label: 'Kegiatan terlaksana',
                    value: $kegiatan,
                    caption: 'Sejak masjid berdiri',
                    icon: 'sparkles',
                ),
                $this->entry(
                    label: 'Materi e-library',
                    value: $materi,
                    caption: 'Slide, audio, dan video kajian',
                    icon: 'folder',
                ),
            ];
        });
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return Highlight
     */
    private function entry(string $label, float|int $value, string $caption, string $icon, string $prefix = '', string $suffix = ''): array
    {
        return [
            'label' => $label,
            'value' => $value,
            'prefix' => $prefix,
            'suffix' => $suffix,
            'display' => $prefix.number_format((float) $value, 0, ',', '.').$suffix,
            'caption' => $caption,
            'icon' => $icon,
        ];
    }
}
