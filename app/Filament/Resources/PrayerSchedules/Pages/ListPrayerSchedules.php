<?php

namespace App\Filament\Resources\PrayerSchedules\Pages;

use App\Filament\Resources\PrayerSchedules\PrayerScheduleResource;
use App\Services\PrayerScheduleService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Throwable;

class ListPrayerSchedules extends ListRecords
{
    protected static string $resource = PrayerScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncFromApi')
                ->label('Sinkronkan dari API')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Ambil jadwal terbaru dari API?')
                ->modalDescription('Jadwal bulan berjalan dan beberapa bulan berikutnya akan diperbarui. Baris yang ditandai override tidak ikut berubah.')
                ->action(function (PrayerScheduleService $service): void {
                    try {
                        $synced = $service->syncUpcomingMonths();
                    } catch (Throwable $exception) {
                        Notification::make()
                            ->title('Sinkronisasi gagal')
                            ->body($exception->getMessage().' Jadwal lama tetap dipakai; Anda bisa mengisi manual.')
                            ->danger()
                            ->persistent()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title("Berhasil menyinkronkan {$synced} hari jadwal")
                        ->success()
                        ->send();
                }),

            CreateAction::make()->label('Tambah jadwal manual'),
        ];
    }
}
