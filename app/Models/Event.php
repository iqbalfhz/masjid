<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Models\Concerns\HasApprovalWorkflow;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\RecordsActivity;
use App\Models\Concerns\TracksCreator;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Laravel\Scout\Searchable;
use Spatie\Tags\HasTags;

#[Fillable(['title', 'slug', 'description', 'poster_image', 'event_date', 'start_time', 'end_time', 'location', 'category', 'rsvp_enabled', 'rsvp_quota', 'status', 'approval_note', 'created_by', 'reviewed_by', 'reviewed_at'])]
class Event extends Model
{
    use HasApprovalWorkflow, HasFactory, HasSlug, HasTags, RecordsActivity, Searchable, TracksCreator;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'status' => ContentStatus::class,
            'rsvp_enabled' => 'boolean',
            'reviewed_at' => 'datetime',
        ];
    }

    public function rsvps(): MorphMany
    {
        return $this->morphMany(Rsvp::class, 'rsvpable');
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopeUpcoming(Builder $query): void
    {
        $query->whereDate('event_date', '>=', today())->orderBy('event_date');
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'title' => $this->title,
            'description' => strip_tags((string) $this->description),
            'category' => $this->category,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->isApproved();
    }
}
