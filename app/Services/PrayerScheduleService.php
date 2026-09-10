<?php

namespace App\Services;

use App\Models\MosqueSetting;
use App\Models\PrayerSchedule;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Sinkronisasi jadwal sholat dari Aladhan API (PRD 5.1.2 & 5.2.14).
 *
 * Baris yang sudah ditandai `is_override` oleh admin tidak pernah ditimpa,
 * supaya penyesuaian lokal (misal Jumat digeser) tetap bertahan. Bila API tidak
 * bisa dihubungi, jadwal lama tetap dipakai — sesuai mitigasi risiko di PRD 12.
 */
class PrayerScheduleService
{
    /**
     * Ambil jadwal satu bulan penuh dan simpan ke database.
     *
     * @return int jumlah baris yang dibuat atau diperbarui
     */
    public function syncMonth(int $year, int $month): int
    {
        $setting = MosqueSetting::current();

        $response = Http::timeout(20)
            // throw: false agar kegagalan API ditangani dengan pesan sendiri
            // yang bisa dibaca pengurus, bukan RequestException mentah.
            ->retry(2, 500, throw: false)
            ->get(rtrim((string) config('masjid.prayer.base_url'), '/')."/calendar/{$year}/{$month}", [
                'latitude' => $setting->latitude ?? config('masjid.prayer.latitude'),
                'longitude' => $setting->longitude ?? config('masjid.prayer.longitude'),
                'method' => $setting->prayer_calculation_method ?? config('masjid.prayer.method'),
                'school' => config('masjid.prayer.school'),
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Gagal mengambil jadwal sholat dari API (HTTP '.$response->status().').');
        }

        /** @var list<array{timings: array<string, string>, date: array{gregorian: array{date: string}}}> $days */
        $days = $response->json('data', []);

        if ($days === []) {
            throw new RuntimeException('API jadwal sholat tidak mengembalikan data untuk periode tersebut.');
        }

        $synced = 0;

        foreach ($days as $day) {
            $date = Carbon::createFromFormat('d-m-Y', $day['date']['gregorian']['date'])->startOfDay();

            $existing = PrayerSchedule::query()->whereDate('date', $date)->first();

            if ($existing?->is_override) {
                continue;
            }

            PrayerSchedule::query()->updateOrCreate(
                ['date' => $date->toDateString()],
                [
                    'imsak' => $this->time($day['timings'], 'Imsak'),
                    'fajr' => $this->time($day['timings'], 'Fajr'),
                    'sunrise' => $this->time($day['timings'], 'Sunrise'),
                    'dhuhr' => $this->time($day['timings'], 'Dhuhr'),
                    'asr' => $this->time($day['timings'], 'Asr'),
                    'maghrib' => $this->time($day['timings'], 'Maghrib'),
                    'isha' => $this->time($day['timings'], 'Isha'),
                    'is_override' => false,
                ],
            );

            $synced++;
        }

        return $synced;
    }

    /**
     * Sinkronkan bulan berjalan dan beberapa bulan ke depan sekaligus.
     */
    public function syncUpcomingMonths(?int $monthsAhead = null): int
    {
        $monthsAhead ??= (int) config('masjid.prayer.sync_months_ahead');
        $cursor = today()->startOfMonth();
        $synced = 0;

        for ($i = 0; $i <= $monthsAhead; $i++) {
            $synced += $this->syncMonth($cursor->year, $cursor->month);
            $cursor = $cursor->addMonth();
        }

        return $synced;
    }

    public function forDate(CarbonInterface $date): ?PrayerSchedule
    {
        return PrayerSchedule::query()->whereDate('date', $date)->first();
    }

    public function today(): ?PrayerSchedule
    {
        return $this->forDate(today());
    }

    /**
     * Waktu sholat berikutnya hari ini, atau Subuh besok bila Isya sudah lewat.
     *
     * @return array{key: string, label: string, time: Carbon}|null
     */
    public function nextPrayer(?CarbonInterface $now = null): ?array
    {
        $now = $now ? Carbon::parse($now) : now();
        $schedule = $this->forDate($now);

        if ($schedule !== null) {
            foreach (PrayerSchedule::REMINDABLE as $key => $label) {
                $time = Carbon::parse($now->toDateString().' '.$schedule->{$key});

                if ($time->isAfter($now)) {
                    return ['key' => $key, 'label' => $label, 'time' => $time];
                }
            }
        }

        $tomorrow = $this->forDate($now->copy()->addDay());

        if ($tomorrow === null) {
            return null;
        }

        return [
            'key' => 'fajr',
            'label' => PrayerSchedule::REMINDABLE['fajr'],
            'time' => Carbon::parse($tomorrow->date->toDateString().' '.$tomorrow->fajr),
        ];
    }

    /**
     * @param  array<string, string>  $timings
     */
    private function time(array $timings, string $key): ?string
    {
        $raw = $timings[$key] ?? null;

        if ($raw === null) {
            return null;
        }

        // Aladhan mengembalikan format "04:30 (WIB)".
        return substr(trim(explode(' ', $raw)[0]), 0, 5).':00';
    }
}
