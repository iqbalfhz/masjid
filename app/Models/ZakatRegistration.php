<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\ZakatType;
use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['registration_number', 'name', 'phone', 'zakat_type', 'soul_count', 'amount', 'payment_status', 'notes', 'confirmed_by', 'confirmed_at'])]
class ZakatRegistration extends Model
{
    use HasFactory, RecordsActivity;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'zakat_type' => ZakatType::class,
            'payment_status' => PaymentStatus::class,
            'soul_count' => 'integer',
            'amount' => 'decimal:2',
            'confirmed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $registration): void {
            $registration->registration_number ??= static::generateNumber();
        });
    }

    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public static function generateNumber(): string
    {
        do {
            $number = 'ZKT-'.now()->format('Ymd').'-'.Str::upper(Str::random(4));
        } while (static::query()->where('registration_number', $number)->exists());

        return $number;
    }
}
