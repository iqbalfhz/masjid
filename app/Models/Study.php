<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Enums\ScheduleType;
use App\Models\Concerns\HasApprovalWorkflow;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\RecordsActivity;
use App\Models\Concerns\TracksCreator;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Laravel\Scout\Searchable;
use Spatie\Tags\HasTags;

#[Fillable(['theme', 'slug', 'ustadz_name', 'schedule_type', 'day_of_week', 'start_date', 'time', 'end_time', 'location', 'description', 'poster_image', 'rsvp_enabled', 'rsvp_quota', 'status', 'approval_note', 'created_by', 'reviewed_by', 'reviewed_at'])]
class Study extends Model
{
    use HasApprovalWorkflow, HasFactory, HasSlug, HasTags, RecordsActivity, Searchable, TracksCreator;

    /**
     * Nama hari dalam bahasa Indonesia, index mengikuti Carbon (0 = Minggu).
     *
     * @var array<int, string>
     */
    public const DAYS = [
        0 => 'Minggu',
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'status' => ContentStatus::class,
            'schedule_type' => ScheduleType::class,
            'rsvp_enabled' => 'boolean',
            'reviewed_at' => 'datetime',
        ];
    }

    public function slugSourceColumn(): string
    {
        return 'theme';
    }

    public function rsvps(): MorphMany
    {
        return $this->morphMany(Rsvp::class, 'rsvpable');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(LibraryMaterial::class);
    }

    public function dayName(): ?string
    {
        return $this->day_of_week === null ? null : self::DAYS[$this->day_of_week];
    }

    public function scheduleLabel(): string
    {
        $time = substr((string) $this->time, 0, 5);

        return $this->schedule_type === ScheduleType::Rutin
            ? sprintf('Setiap %s, %s WIB', $this->dayName() ?? '-', $time)
            : sprintf('%s, %s WIB', $this->start_date?->translatedFormat('d F Y') ?? '-', $time);
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'theme' => $this->theme,
            'ustadz_name' => $this->ustadz_name,
            'description' => strip_tags((string) $this->description),
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->isApproved();
    }
}
