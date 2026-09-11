<?php

namespace App\Filament\Widgets;

use App\Enums\ContentStatus;
use App\Enums\TransactionType;
use App\Models\Event;
use App\Models\FinanceTransaction;
use App\Models\Study;
use App\Services\PrayerScheduleService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Ringkasan kondisi masjid: keuangan bulan berjalan, kajian aktif, dan waktu
 * sholat berikutnya.
 *
 * Singkatan zona waktu diturunkan dari config app.timezone lewat format("T"),
 * bukan ditulis "WIB" secara tetap. Kodebase ini dipasang ulang per masjid
 * (PRD bagian 13), dan masjid di Makassar akan melihat jam WITA-nya diberi
 * label WIB — salah tampil, bukan salah hitung.
 */
class MosqueOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Ringkasan Masjid';

    protected function getStats(): array
    {
        $income = FinanceTransaction::query()
            ->inMonth(today()->year, today()->month)
            ->where('type', TransactionType::In)
            ->sum('amount');

        $expense = FinanceTransaction::query()
            ->inMonth(today()->year, today()->month)
            ->where('type', TransactionType::Out)
            ->sum('amount');

        $activeStudies = Study::query()->where('status', ContentStatus::Disetujui)->count();
        $upcomingEvents = Event::query()->where('status', ContentStatus::Disetujui)->upcoming()->count();
        $next = app(PrayerScheduleService::class)->nextPrayer();

        return [
            Stat::make('Pemasukan bulan ini', 'Rp '.number_format((float) $income, 0, ',', '.'))
                ->description(today()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->color('success')
                ->url(route('filament.admin.resources.finance-transactions.index')),

            Stat::make('Pengeluaran bulan ini', 'Rp '.number_format((float) $expense, 0, ',', '.'))
                ->description('Saldo: Rp '.number_format((float) $income - (float) $expense, 0, ',', '.'))
                ->descriptionIcon('heroicon-o-arrow-trending-down')
                ->color((float) $income >= (float) $expense ? 'success' : 'danger')
                ->url(route('filament.admin.resources.finance-transactions.index')),

            Stat::make('Kajian & kegiatan tayang', $activeStudies)
                ->description("{$upcomingEvents} kegiatan akan datang")
                ->descriptionIcon('heroicon-o-book-open')
                ->color('info')
                ->url(route('filament.admin.resources.studies.index')),

            Stat::make('Sholat berikutnya', $next === null ? '—' : $next['label'])
                ->description($next === null
                    ? 'Jadwal belum tersinkron'
                    : $next['time']->format('H:i T'))
                ->descriptionIcon('heroicon-o-clock')
                ->color('primary')
                ->url(route('filament.admin.resources.prayer-schedules.index')),
        ];
    }
}
