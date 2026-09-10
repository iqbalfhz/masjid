<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Tags\Tag as SpatieTag;

/**
 * Tag lintas modul (PRD 5.1.18).
 *
 * spatie/laravel-tags menyimpan `name` dan `slug` sebagai kolom JSON
 * multi-bahasa. Model ini menambahkan route model binding berbasis slug agar
 * URL jelajah tag tetap ramah dibaca, misal `/tag/ramadhan`.
 */
class Tag extends SpatieTag
{
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getRouteKey(): string
    {
        return (string) $this->slug;
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopeWithSlug(Builder $query, string $slug): void
    {
        $query->where('slug->'.app()->getLocale(), $slug);
    }

    public function resolveRouteBinding($value, $field = null): ?self
    {
        return static::query()->withSlug((string) $value)->first();
    }
}
