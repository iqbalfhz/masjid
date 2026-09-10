<?php

namespace App\Filament\Resources\QurbanRegistrations\Pages;

use App\Filament\Resources\QurbanRegistrations\QurbanRegistrationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListQurbanRegistrations extends ListRecords
{
    protected static string $resource = QurbanRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
