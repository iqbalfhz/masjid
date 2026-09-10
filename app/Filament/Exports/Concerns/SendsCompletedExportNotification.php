<?php

namespace App\Filament\Exports\Concerns;

use Filament\Actions\Exports\Models\Export;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

/**
 * Notifikasi selesai export yang tidak menghalangi layar.
 *
 * Karena export dijalankan langsung (bukan lewat antrean), Filament membuat
 * toast-nya menetap selamanya — sebab toast itulah satu-satunya tempat tautan
 * unduhan berada. Akibatnya kotak notifikasi menumpuk dan harus ditutup manual.
 *
 * Di sini toast dibuat menghilang sendiri, tapi salinannya sekaligus dikirim ke
 * lonceng notifikasi supaya tautan unduhannya tidak ikut hilang dan tetap bisa
 * diambil kapan saja.
 */
trait SendsCompletedExportNotification
{
    /**
     * Berapa lama toast bertahan sebelum menghilang sendiri.
     */
    protected const NOTIFICATION_SECONDS = 8;

    public static function modifyCompletedNotification(Notification $notification, Export $export): Notification
    {
        $penerima = $export->user ?? Auth::user();

        if ($penerima !== null) {
            // Salinan permanen di lonceng: tautan unduhan tetap tersimpan
            // meski toast-nya sudah lewat.
            Notification::make()
                ->title($notification->getTitle())
                ->body($notification->getBody())
                ->icon('heroicon-o-arrow-down-tray')
                ->iconColor('success')
                ->actions($notification->getActions())
                ->sendToDatabase($penerima);
        }

        return $notification->seconds(self::NOTIFICATION_SECONDS);
    }
}
