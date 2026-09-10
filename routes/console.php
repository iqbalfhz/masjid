<?php

use App\Console\Commands\SendPrayerReminders;
use App\Console\Commands\SyncPrayerSchedules;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Tugas Terjadwal
|--------------------------------------------------------------------------
|
| Jalankan dengan `php artisan schedule:work` saat development, atau lewat
| cron `* * * * * php artisan schedule:run` di production.
|
*/

// Ambil jadwal sholat terbaru tiap dini hari, di luar jam ramai.
Schedule::command(SyncPrayerSchedules::class)
    ->dailyAt('01:30')
    ->withoutOverlapping()
    ->onOneServer();

// Cek tiap menit apakah ada jamaah yang perlu diingatkan menjelang sholat.
Schedule::command(SendPrayerReminders::class)
    ->everyMinute()
    ->withoutOverlapping();
