<?php

namespace App\Console\Commands;

use App\Models\MosqueSetting;
use App\Models\PushSubscription;
use App\Services\PrayerScheduleService;
use App\Services\WebPushService;
use Illuminate\Console\Command;

/**
 * Kirim reminder menjelang waktu sholat ke jamaah yang berlangganan (PRD 5.1.2).
 *
 * Dijalankan tiap menit oleh scheduler; pengiriman hanya terjadi bila selisih
 * waktu sekarang ke waktu sholat berikutnya persis sama dengan preferensi
 * "berapa menit sebelum" milik jamaah tersebut.
 */
class SendPrayerReminders extends Command
{
    protected $signature = 'masjid:send-prayer-reminders';

    protected $description = 'Kirim notifikasi pengingat menjelang waktu sholat kepada jamaah yang berlangganan';

    public function handle(PrayerScheduleService $prayers, WebPushService $push): int
    {
        if (! $push->isConfigured()) {
            $this->components->warn('Kunci VAPID belum diisi. Jalankan: php artisan masjid:vapid-keys');

            return self::SUCCESS;
        }

        $settings = MosqueSetting::current()->prayer_reminder_settings ?? [];

        if (! ($settings['enabled'] ?? false)) {
            return self::SUCCESS;
        }

        $next = $prayers->nextPrayer();

        if ($next === null) {
            return self::SUCCESS;
        }

        $enabledPrayers = $settings['prayers'] ?? [];

        if ($enabledPrayers !== [] && ! in_array($next['key'], $enabledPrayers, true)) {
            return self::SUCCESS;
        }

        $minutesAway = (int) now()->diffInMinutes($next['time'], absolute: true);
        $sent = 0;

        PushSubscription::query()
            ->where('minutes_before', $minutesAway)
            ->where(fn ($query) => $query->whereNull('last_notified_at')->orWhere('last_notified_at', '<', now()->subMinutes(20)))
            ->chunkById(100, function ($subscriptions) use ($push, $next, &$sent): void {
                foreach ($subscriptions as $subscription) {
                    if (! $subscription->wantsPrayer($next['key'])) {
                        continue;
                    }

                    if ($push->send($subscription)) {
                        $subscription->forceFill(['last_notified_at' => now()])->save();
                        $sent++;
                    }
                }
            });

        if ($sent > 0) {
            $this->components->info("Mengirim {$sent} pengingat {$next['label']}.");
        }

        return self::SUCCESS;
    }
}
