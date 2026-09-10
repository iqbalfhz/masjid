<?php

namespace App\Models;

use App\Enums\TransactionType;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'type', 'description', 'is_active'])]
class FinanceCategory extends Model
{
    use HasFactory, HasSlug, RecordsActivity;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'is_active' => 'boolean',
        ];
    }

    public function slugSourceColumn(): string
    {
        return 'name';
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FinanceTransaction::class);
    }
}
