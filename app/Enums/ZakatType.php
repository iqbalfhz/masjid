<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ZakatType: string implements HasLabel
{
    case Fitrah = 'fitrah';
    case Maal = 'maal';

    public function getLabel(): string
    {
        return match ($this) {
            self::Fitrah => 'Zakat Fitrah',
            self::Maal => 'Zakat Maal',
        };
    }
}
