<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

#[Fillable(['question', 'answer', 'category', 'sort_order', 'is_published'])]
class Faq extends Model
{
    use HasFactory, RecordsActivity, Searchable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true)->orderBy('sort_order');
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'question' => $this->question,
            'answer' => strip_tags((string) $this->answer),
            'category' => $this->category,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return (bool) $this->is_published;
    }
}
