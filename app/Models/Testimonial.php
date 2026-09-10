<?php

namespace App\Models;

use App\Enums\ModerationStatus;
use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['name', 'message', 'status', 'ip_address', 'moderated_by', 'moderated_at'])]
class Testimonial extends Model
{
    use HasFactory, RecordsActivity;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ModerationStatus::class,
            'moderated_at' => 'datetime',
        ];
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopeApproved(Builder $query): void
    {
        $query->where('status', ModerationStatus::Disetujui);
    }

    public function displayName(): string
    {
        return $this->name ?: 'Jamaah';
    }

    public function moderateBy(User $moderator, ModerationStatus $status): void
    {
        $this->forceFill([
            'status' => $status,
            'moderated_by' => $moderator->getKey(),
            'moderated_at' => now(),
        ])->save();
    }
}
