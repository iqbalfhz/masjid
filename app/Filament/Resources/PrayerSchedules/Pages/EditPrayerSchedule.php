<?php

namespace App\Filament\Resources\PrayerSchedules\Pages;

use App\Filament\Resources\PrayerSchedules\PrayerScheduleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPrayerSchedule extends EditRecord
{
    protected static string $resource = PrayerScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
