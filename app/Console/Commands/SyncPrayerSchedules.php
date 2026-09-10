<?php

namespace App\Console\Commands;

use App\Services\PrayerScheduleService;
use Illuminate\Console\Command;
use Throwable;

class SyncPrayerSchedules extends Command
{
    protected $signature = 'masjid:sync-prayer-schedules
                            {--months= : Berapa bulan ke depan yang ikut disinkronkan}';

    protected $description = 'Sinkronkan jadwal sholat dari API ke database (bulan berjalan dan beberapa bulan berikutnya)';

    public function handle(PrayerScheduleService $service): int
    {
        $months = $this->option('months');

        try {
            $synced = $service->syncUpcomingMonths($months === null ? null : (int) $months);
        } catch (Throwable $exception) {
            $this->components->error('Sinkronisasi gagal: '.$exception->getMessage());
            $this->components->warn('Jadwal lama tetap dipakai. Admin bisa mengisi manual lewat menu Jadwal Sholat.');

            return self::FAILURE;
        }

        $this->components->info("Berhasil menyinkronkan {$synced} hari jadwal sholat.");

        return self::SUCCESS;
    }
}
