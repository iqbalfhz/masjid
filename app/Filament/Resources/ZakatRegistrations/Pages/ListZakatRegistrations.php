<?php

namespace App\Filament\Resources\ZakatRegistrations\Pages;

use App\Filament\Resources\ZakatRegistrations\ZakatRegistrationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListZakatRegistrations extends ListRecords
{
    protected static string $resource = ZakatRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
