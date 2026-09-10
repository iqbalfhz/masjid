<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum QurbanServiceType: string implements HasLabel
{
    case Kurban = 'kurban';
    case Aqiqah = 'aqiqah';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }
}
