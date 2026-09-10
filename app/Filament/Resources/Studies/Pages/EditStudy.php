<?php

namespace App\Filament\Resources\Studies\Pages;

use App\Filament\Resources\Studies\StudyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStudy extends EditRecord
{
    protected static string $resource = StudyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
