<?php

namespace App\Filament\Resources\LibraryMaterials\Pages;

use App\Filament\Resources\LibraryMaterials\LibraryMaterialResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLibraryMaterials extends ListRecords
{
    protected static string $resource = LibraryMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
