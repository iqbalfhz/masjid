<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Mengisi kolom `slug` otomatis dari atribut sumber ketika masih kosong,
 * lalu menjamin keunikannya dengan menambahkan sufiks angka bila perlu.
 */
trait HasSlug
{
    public static function bootHasSlug(): void
    {
        static::saving(function (Model $model): void {
            /** @var static $model */
            if (blank($model->slug)) {
                $model->slug = $model->generateUniqueSlug((string) $model->{$model->slugSourceColumn()});
            }
        });
    }

    public function slugSourceColumn(): string
    {
        return 'title';
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function generateUniqueSlug(string $source): string
    {
        $base = Str::slug($source) ?: Str::random(8);
        $slug = $base;
        $suffix = 1;

        while (static::query()->where('slug', $slug)->whereKeyNot($this->getKey())->exists()) {
            $slug = $base.'-'.++$suffix;
        }

        return $slug;
    }
}
