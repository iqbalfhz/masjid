<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use App\Models\Concerns\TracksCreator;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['date', 'imsak', 'fajr', 'sunrise', 'dhuhr', 'asr', 'maghrib', 'isha', 'is_override', 'note', 'created_by'])]
class PrayerSchedule extends Model
{
    use HasFactory, RecordsActivity, TracksCreator;

    /**
     * Waktu sholat yang ditampilkan ke jamaah, berurutan sepanjang hari.
     *
     * @var array<string, string>
     */
    public const PRAYERS = [
        'imsak' => 'Imsak',
        'fajr' => 'Subuh',
        'sunrise' => 'Syuruq',
        'dhuhr' => 'Dzuhur',
        'asr' => 'Ashar',
        'maghrib' => 'Maghrib',
        'isha' => 'Isya',
    ];

    /**
     * Waktu yang bisa dijadikan reminder push notification (tanpa syuruq).
     *
     * @var array<string, string>
     */
    public const REMINDABLE = [
        'fajr' => 'Subuh',
        'dhuhr' => 'Dzuhur',
        'asr' => 'Ashar',
        'maghrib' => 'Maghrib',
        'isha' => 'Isya',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_override' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopeForMonth(Builder $query, int $year, int $month): void
    {
        $query->whereYear('date', $year)->whereMonth('date', $month);
    }

    /**
     * @return array<string, string>
     */
    public function times(): array
    {
        return collect(self::PRAYERS)
            ->mapWithKeys(fn (string $label, string $key): array => [$key => (string) $this->{$key}])
            ->filter()
            ->all();
    }
}
