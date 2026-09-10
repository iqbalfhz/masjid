<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TransactionType: string implements HasColor, HasLabel
{
    case In = 'in';
    case Out = 'out';

    public function getLabel(): string
    {
        return match ($this) {
            self::In => 'Pemasukan',
            self::Out => 'Pengeluaran',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::In => 'success',
            self::Out => 'danger',
        };
    }
}
