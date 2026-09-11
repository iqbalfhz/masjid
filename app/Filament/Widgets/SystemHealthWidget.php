<?php

namespace App\Filament\Widgets;

use App\Models\MosqueSetting;
use App\Models\PrayerSchedule;
use App\Models\PushSubscription;
use App\Services\WebPushService;
use Carbon\CarbonImmutable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

/**
 * Kesehatan pekerjaan terjadwal yang menopang masjid di latar belakang.
 *
 * Dua perintah di routes/console.php berjalan lewat cron: sinkron jadwal sholat
 * dan pengiriman reminder. Keduanya tidak punya tempat melapor. Bila cron mati
 * di server, API Aladhan berubah, atau kunci VAPID belum diisi, jadwal di
 * website publik jadi basi dan reminder berhenti — dan yang pertama tahu adalah
 * jamaah yang salah datang waktu subuh, bukan pengurus. Tim DKM tidak punya
 * akses SSH untuk memeriksanya.
 *
 * Sinyalnya diturunkan dari data yang memang sudah ditulis sistem saat bekerja
 * normal, sehingga tidak perlu tabel heartbeat baru: berapa hari ke depan
 * jadwal masih tersedia, dan kapan reminder terakhir benar-benar terkirim.
 * Begitu angkanya menuju nol, ada yang rusak.
 */
class SystemHealthWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 6;

    protected ?string $heading = 'Kesehatan Sistem';

    protected ?string $description = 'Pekerjaan latar belakang yang menjaga jadwal dan pengingat tetap hidup.';

    public static function canView(): bool
    {
        // Hanya berguna bagi yang bisa menindaklanjuti: mengatur ulang jadwal
        // atau menghubungi pengelola server.
        return Auth::user()?->can('view_any:prayer_schedule') === true;
    }

    protected function getStats(): array
    {
        return [
            $this->prayerScheduleCoverage(),
            $this->reminderStatus(),
        ];
    }

    private function prayerScheduleCoverage(): Stat
    {
        $lastDate = PrayerSchedule::query()->max('date');
        $daysLeft = $lastDate === null
            ? 0
            : (int) today()->diffInDays($lastDate, absolute: false);

        $color = match (true) {
            $daysLeft <= 0 => 'danger',
            $daysLeft <= 3 => 'warning',
            default => 'success',
        };

        return Stat::make('Jadwal sholat tersedia', $daysLeft <= 0 ? 'Habis' : $daysLeft.' hari lagi')
            ->description($lastDate === null
                ? 'Belum pernah tersinkron — jalankan sinkronisasi'
                : 'Sampai '.CarbonImmutable::parse($lastDate)->translatedFormat('d F Y'))
            ->descriptionIcon($daysLeft <= 3 ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-calendar-days')
            ->color($color)
            ->url(route('filament.admin.resources.prayer-schedules.index'));
    }

    private function reminderStatus(): Stat
    {
        if (! app(WebPushService::class)->isConfigured()) {
            return Stat::make('Pengingat sholat', 'Belum aktif')
                ->description('Kunci VAPID belum dibuat')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color('warning');
        }

        $enabled = MosqueSetting::current()->prayer_reminder_settings['enabled'] ?? false;

        if (! $enabled) {
            return Stat::make('Pengingat sholat', 'Dimatikan')
                ->description('Dinonaktifkan lewat Pengaturan')
                ->descriptionIcon('heroicon-o-bell-slash')
                ->color('gray');
        }

        $subscribers = PushSubscription::query()->count();
        $lastSent = PushSubscription::query()->max('last_notified_at');

        return Stat::make('Pengingat sholat', $subscribers.' jamaah berlangganan')
            ->description($lastSent === null
                ? 'Belum ada pengingat terkirim'
                : 'Terakhir terkirim '.CarbonImmutable::parse($lastSent)->diffForHumans())
            ->descriptionIcon('heroicon-o-bell-alert')
            ->color($subscribers > 0 ? 'success' : 'gray');
    }
}
