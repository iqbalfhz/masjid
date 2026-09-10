<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\QurbanServiceType;
use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['registration_number', 'name', 'phone', 'service_type', 'animal_type', 'quantity', 'amount', 'payment_status', 'notes', 'confirmed_by', 'confirmed_at'])]
class QurbanRegistration extends Model
{
    use HasFactory, RecordsActivity;

    /**
     * Jenis hewan yang bisa dipilih jamaah pada form pendaftaran.
     *
     * @var array<string, string>
     */
    public const ANIMAL_TYPES = [
        'kambing' => 'Kambing',
        'domba' => 'Domba',
        'sapi_utuh' => 'Sapi (utuh)',
        'sapi_patungan' => 'Sapi (patungan 1/7)',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'service_type' => QurbanServiceType::class,
            'payment_status' => PaymentStatus::class,
            'quantity' => 'integer',
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

    public function animalLabel(): string
    {
        return self::ANIMAL_TYPES[$this->animal_type] ?? $this->animal_type;
    }

    public static function generateNumber(): string
    {
        do {
            $number = 'QRB-'.now()->format('Ymd').'-'.Str::upper(Str::random(4));
        } while (static::query()->where('registration_number', $number)->exists());

        return $number;
    }
}
