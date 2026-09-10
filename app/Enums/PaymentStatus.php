<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PaymentStatus: string implements HasColor, HasLabel
{
    case BelumBayar = 'belum_bayar';
    case Lunas = 'lunas';

    public function getLabel(): string
    {
        return match ($this) {
            self::BelumBayar => 'Belum Bayar',
            self::Lunas => 'Lunas',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::BelumBayar => 'warning',
            self::Lunas => 'success',
        };
    }
}
