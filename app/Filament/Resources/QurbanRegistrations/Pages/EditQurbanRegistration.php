<?php

namespace App\Filament\Resources\QurbanRegistrations\Pages;

use App\Filament\Resources\QurbanRegistrations\QurbanRegistrationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditQurbanRegistration extends EditRecord
{
    protected static string $resource = QurbanRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
