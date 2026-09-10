<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Model;

/**
 * Pengaturan umum masjid — tabel singleton, selalu memakai baris pertama.
 */
#[Unguarded]
class MosqueSetting extends Model
{
    use RecordsActivity;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'prayer_reminder_settings' => 'array',
            'social_links' => 'array',
        ];
    }

    public static function current(): self
    {
        return once(fn (): self => static::query()->firstOrCreate([]));
    }
}
