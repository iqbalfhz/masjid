<?php

namespace App\Models;

use App\Enums\SuggestionCategory;
use App\Enums\SuggestionStatus;
use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['ticket_code', 'name', 'contact', 'category', 'message', 'status', 'response_note', 'handled_by', 'handled_at'])]
class Suggestion extends Model
{
    use HasFactory, RecordsActivity;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => SuggestionCategory::class,
            'status' => SuggestionStatus::class,
            'handled_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $suggestion): void {
            $suggestion->ticket_code ??= static::generateTicketCode();
        });
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function isAnonymous(): bool
    {
        return blank($this->name);
    }

    public static function generateTicketCode(): string
    {
        do {
            $code = 'SR-'.now()->format('Ymd').'-'.Str::upper(Str::random(4));
        } while (static::query()->where('ticket_code', $code)->exists());

        return $code;
    }
}
