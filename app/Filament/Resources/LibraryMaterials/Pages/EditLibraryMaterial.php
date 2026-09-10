<?php

namespace App\Filament\Resources\LibraryMaterials\Pages;

use App\Filament\Resources\LibraryMaterials\LibraryMaterialResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLibraryMaterial extends EditRecord
{
    protected static string $resource = LibraryMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
