<?php

namespace App\View\Composers;

use App\Models\Announcement;
use App\Models\MosqueSetting;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Data yang selalu dibutuhkan halaman publik: identitas masjid, menu navigasi,
 * dan pengumuman aktif untuk running text.
 *
 * Composer ini dipasang ke layout sekaligus seluruh view di bawah `public.*`
 * karena Blade mengevaluasi view anak lebih dulu daripada layout induknya.
 * Hasil query di-memoize di instance (composer dibinding sebagai singleton),
 * jadi tetap satu query per request.
 */
class PublicLayoutComposer
{
    private ?MosqueSetting $setting = null;

    /** @var Collection<int, Announcement>|null */
    private ?Collection $announcements = null;

    /** @var list<array{label: string, url: string, active: bool}>|null */
    private ?array $navigation = null;

    public function compose(View $view): void
    {
        $view->with([
            'setting' => $this->setting ??= MosqueSetting::current(),
            'navigation' => $this->navigation ??= $this->navigation(),
            'runningAnnouncements' => $this->announcements ??= Announcement::query()
                ->active()
                ->latest('start_date')
                ->take(5)
                ->get(),
        ]);
    }

    /**
     * @return list<array{label: string, url: string, active: bool}>
     */
    private function navigation(): array
    {
        /** @var array<string, string> $items nama route => label menu */
        $items = [
            'home' => 'Beranda',
            'jadwal-sholat' => 'Jadwal Sholat',
            'kajian.index' => 'Kajian',
            'laporan-keuangan' => 'Keuangan',
            'artikel.index' => 'Artikel',
            'galeri.index' => 'Galeri',
            'profil' => 'Profil',
            'faq' => 'FAQ',
            'kontak' => 'Kontak',
        ];

        return collect($items)
            ->map(fn (string $label, string $route): array => [
                'label' => $label,
                'url' => route($route),
                'active' => request()->routeIs($route)
                    || (str_contains($route, '.') && request()->routeIs(str($route)->before('.')->value().'.*')),
            ])
            ->values()
            ->all();
    }
}
