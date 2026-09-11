<?php

namespace App\Support;

use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification as NotificationDispatcher;

/**
 * Pengantar notifikasi database yang tidak bergantung pada queue worker.
 *
 * `Filament\Notifications\DatabaseNotification` mengimplementasikan ShouldQueue,
 * jadi `Notification::sendToDatabase()` selalu dilempar ke antrean. Di server
 * masjid yang tidak menjalankan `queue:work` — kondisi wajar untuk hosting
 * sederhana — notifikasi itu hanya menumpuk di tabel `jobs` dan lonceng
 * pengurus tak pernah berisi.
 *
 * Menyimpan notifikasi hanyalah satu INSERT: mengantrekannya tidak memberi
 * keuntungan apa pun, sementara risikonya notifikasi tidak pernah sampai.
 * Karena itu pengirimannya dipaksa langsung, sejalan dengan keputusan yang
 * sama pada Exporter (`getJobConnection()` mengembalikan `sync`).
 */
class ImmediateDatabaseNotification
{
    /**
     * @param  Collection<int, Model>|array<int, Model>|Model  $recipients
     */
    public static function deliver(Notification $notification, Collection|array|Model $recipients): void
    {
        NotificationDispatcher::sendNow($recipients, $notification->toDatabase());
    }
}
