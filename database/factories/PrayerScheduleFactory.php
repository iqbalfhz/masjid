<?php

namespace Database\Factories;

use App\Models\PrayerSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PrayerSchedule>
 */
class PrayerScheduleFactory extends Factory
{
    protected $model = PrayerSchedule::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => today(),
            'imsak' => '04:20:00',
            'fajr' => '04:30:00',
            'sunrise' => '05:45:00',
            'dhuhr' => '11:55:00',
            'asr' => '15:15:00',
            'maghrib' => '18:05:00',
            'isha' => '19:15:00',
            'is_override' => false,
        ];
    }

    public function override(): static
    {
        return $this->state(fn (): array => ['is_override' => true]);
    }
}
