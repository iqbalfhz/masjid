<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'description'])]
class ArticleCategory extends Model
{
    use HasFactory, HasSlug, RecordsActivity;

    public function slugSourceColumn(): string
    {
        return 'name';
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }
}
