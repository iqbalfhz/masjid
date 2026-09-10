<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SuggestionStatus: string implements HasColor, HasLabel
{
    case Baru = 'baru';
    case Diproses = 'diproses';
    case Selesai = 'selesai';

    public function getLabel(): string
    {
        return match ($this) {
            self::Baru => 'Baru',
            self::Diproses => 'Diproses',
            self::Selesai => 'Selesai',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Baru => 'warning',
            self::Diproses => 'info',
            self::Selesai => 'success',
        };
    }
}
