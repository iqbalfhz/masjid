<?php

namespace App\Listeners;

use Filament\Actions\Action;
use Livewire\Component;

/**
 * Menyegarkan badge navigasi begitu sebuah aksi selesai dijalankan.
 *
 * Sidebar adalah komponen Livewire tersendiri, terpisah dari komponen halaman.
 * Saat pengurus menyetujui pengumuman lewat aksi tabel, yang dirender ulang
 * hanya tabelnya — sidebar tidak ikut, sehingga angka "menunggu approval" tetap
 * menampilkan nilai lama sampai halaman dimuat ulang manual.
 *
 * Filament sudah menyiapkan listener `refresh-sidebar` pada komponen itu; yang
 * belum ada hanyalah pihak yang memicunya.
 *
 * Pemicunya sengaja dipasang di event `ActionCalled`, bukan lewat hook
 * `Action::after()`. Hook `after()` bersifat menimpa, jadi pemasangan global
 * akan hilang diam-diam begitu sebuah aksi mendefinisikan `after()`-nya
 * sendiri. Event ini selalu terkirim dari blok `finally`, sehingga tidak bisa
 * dilewati dan berlaku untuk setiap aksi — termasuk aksi baru yang ditambahkan
 * kemudian tanpa perlu mengingat langkah ini.
 *
 * Filament mengirimnya lewat `Event::dispatch(ActionCalled::class, $this)`, yaitu
 * nama event dengan Action sebagai payload — bukan objek event. Karena itu
 * listener ini menerima Action dan harus didaftarkan manual di AppServiceProvider:
 * penemuan otomatis Laravel menyimpulkan nama event dari type-hint, dan di sini
 * type-hint-nya bukan nama event-nya.
 */
class RefreshNavigationBadges
{
    public function handle(Action $action): void
    {
        $livewire = $action->getLivewire();

        if (! $livewire instanceof Component) {
            return;
        }

        $livewire->dispatch('refresh-sidebar');
    }
}
