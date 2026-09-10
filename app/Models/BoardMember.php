<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'position', 'photo', 'bio', 'period_start', 'period_end', 'sort_order', 'is_active'])]
class BoardMember extends Model
{
    use HasFactory, RecordsActivity;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'period_start' => 'integer',
            'period_end' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort_order');
    }

    public function periodLabel(): string
    {
        return $this->period_end
            ? sprintf('%d - %d', $this->period_start, $this->period_end)
            : sprintf('%d - sekarang', $this->period_start);
    }
}
