<?php

namespace App\Filament\Resources\Suggestions\Pages;

use App\Enums\SuggestionStatus;
use App\Filament\Resources\Suggestions\SuggestionResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditSuggestion extends EditRecord
{
    protected static string $resource = SuggestionResource::class;

    /**
     * Catat siapa pengurus yang menindaklanjuti begitu status bergerak dari "baru".
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['status'] ?? null) !== SuggestionStatus::Baru->value) {
            $data['handled_by'] = Auth::id();
            $data['handled_at'] = now();
        }

        return $data;
    }
}
