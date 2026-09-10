<?php

namespace App\Models;

use App\Enums\TransactionType;
use App\Models\Concerns\RecordsActivity;
use App\Models\Concerns\TracksCreator;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['date', 'type', 'finance_category_id', 'amount', 'description', 'reference_no', 'created_by'])]
class FinanceTransaction extends Model
{
    use HasFactory, RecordsActivity, TracksCreator;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'type' => TransactionType::class,
            'amount' => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FinanceCategory::class, 'finance_category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopeInMonth(Builder $query, int $year, int $month): void
    {
        $query->whereYear('date', $year)->whereMonth('date', $month);
    }
}
