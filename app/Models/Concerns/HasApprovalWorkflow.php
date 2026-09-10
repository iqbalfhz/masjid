<?php

namespace App\Models\Concerns;

use App\Enums\ContentStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Alur approval bersama untuk Pengumuman, Kajian, Kegiatan, dan Artikel
 * (PRD 5.2): draft → menunggu approval → disetujui/ditolak, lengkap dengan
 * jejak siapa yang membuat dan siapa yang meninjau.
 *
 * @property ContentStatus $status
 */
trait HasApprovalWorkflow
{
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopeApproved(Builder $query): void
    {
        $query->where('status', ContentStatus::Disetujui);
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopeAwaitingApproval(Builder $query): void
    {
        $query->where('status', ContentStatus::MenungguApproval);
    }

    public function isApproved(): bool
    {
        return $this->status === ContentStatus::Disetujui;
    }

    public function isAwaitingApproval(): bool
    {
        return $this->status === ContentStatus::MenungguApproval;
    }

    public function isEditableByAuthor(): bool
    {
        return in_array($this->status, [ContentStatus::Draft, ContentStatus::Ditolak], true);
    }

    public function submitForApproval(): void
    {
        $this->forceFill([
            'status' => ContentStatus::MenungguApproval,
            'approval_note' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ])->save();
    }

    public function approveBy(User $reviewer, ?string $note = null): void
    {
        $this->forceFill([
            'status' => ContentStatus::Disetujui,
            'approval_note' => $note,
            'reviewed_by' => $reviewer->getKey(),
            'reviewed_at' => now(),
        ])->save();
    }

    public function rejectBy(User $reviewer, string $note): void
    {
        $this->forceFill([
            'status' => ContentStatus::Ditolak,
            'approval_note' => $note,
            'reviewed_by' => $reviewer->getKey(),
            'reviewed_at' => now(),
        ])->save();
    }
}
