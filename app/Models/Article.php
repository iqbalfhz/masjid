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
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;
use Spatie\Tags\HasTags;

#[Fillable(['title', 'slug', 'excerpt', 'content', 'cover_image', 'article_category_id', 'publish_date', 'views', 'status', 'approval_note', 'created_by', 'reviewed_by', 'reviewed_at'])]
class Article extends Model
{
    use HasApprovalWorkflow, HasFactory, HasSlug, HasTags, RecordsActivity, Searchable, TracksCreator;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'publish_date' => 'date',
            'status' => ContentStatus::class,
            'views' => 'integer',
            'reviewed_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'article_category_id');
    }

    /**
     * Artikel yang boleh tampil publik: disetujui dan sudah lewat tanggal publish
     * (PRD 5.1.8).
     *
     * @param  Builder<static>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->approved()->whereDate('publish_date', '<=', today());
    }

    public function isPublished(): bool
    {
        return $this->isApproved() && $this->publish_date !== null && ! $this->publish_date->isFuture();
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'title' => $this->title,
            'excerpt' => (string) $this->excerpt,
            'content' => strip_tags((string) $this->content),
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->isPublished();
    }
}
