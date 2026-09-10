<?php

namespace App\Models;

use App\Enums\AnnouncementPriority;
use App\Enums\ContentStatus;
use App\Models\Concerns\HasApprovalWorkflow;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\RecordsActivity;
use App\Models\Concerns\TracksCreator;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

#[Fillable(['title', 'slug', 'content', 'start_date', 'end_date', 'priority', 'status', 'approval_note', 'created_by', 'reviewed_by', 'reviewed_at'])]
class Announcement extends Model
{
    use HasApprovalWorkflow, HasFactory, HasSlug, RecordsActivity, Searchable, TracksCreator;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => ContentStatus::class,
            'priority' => AnnouncementPriority::class,
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * Pengumuman yang sudah disetujui dan masih dalam rentang tayang.
     *
     * @param  Builder<static>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->approved()
            ->whereDate('start_date', '<=', today())
            ->where(function (Builder $query): void {
                $query->whereNull('end_date')->orWhereDate('end_date', '>=', today());
            });
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'title' => $this->title,
            'content' => strip_tags((string) $this->content),
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->isApproved();
    }
}
