<?php

namespace App\Filament\Resources\ZakatRegistrations\Pages;

use App\Filament\Resources\ZakatRegistrations\ZakatRegistrationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditZakatRegistration extends EditRecord
{
    protected static string $resource = ZakatRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
