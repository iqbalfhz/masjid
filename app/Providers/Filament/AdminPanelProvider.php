<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Http\Middleware\SecurityHeaders;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            /*
             * Halaman profil bawaan Filament (PRD 7.1): tiap pengurus bisa
             * mengubah namanya, mengganti password, dan memasang avatar
             * sendiri tanpa perlu minta bantuan Superadmin.
             */
            ->profile()
            ->colors([
                'primary' => Color::Amber,
            ])
            /*
             * Setelah menyimpan, pengurus dikembalikan ke daftar record — alur
             * yang lebih diharapkan tim non-teknis daripada bertahan di form.
             */
            ->resourceCreatePageRedirect('index')
            ->resourceEditPageRedirect('index')
            /*
             * Lonceng notifikasi internal (PRD 5.2.17): tempat approver melihat
             * konten yang menunggu ditinjau, sekretaris melihat testimoni dan
             * kotak saran baru, serta bendahara melihat pendaftaran layanan.
             *
             * Tanpa ini, notifikasi yang dikirim AdminNotifier hanya tersimpan
             * di tabel `notifications` tanpa ada yang bisa membacanya.
             */
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            /*
             * Urutan grup mengikuti seberapa sering dipakai Tim DKM: pekerjaan
             * konten harian di atas, urusan administratif di bawah.
             *
             * Ikon dipasang di tingkat grup, bukan di tiap menu. Filament hanya
             * mengizinkan salah satu, dan pilihan ini sekaligus mengaktifkan
             * penanda hierarki bawaannya: begitu menu tidak berikon, Filament
             * merender sendiri titik bertali (fi-sidebar-item-grouped-border)
             * di bawah ikon grupnya — pola yang sama dengan referensi NADI,
             * tanpa perlu CSS tambahan.
             *
             * Semua grup dapat dilipat, dan hanya satu terbuka pada satu waktu
             * (resources/views/filament/sidebar-accordion.blade.php). Dengan 25
             * menu, membuka semuanya sekaligus membuat sidebar jauh lebih
             * panjang daripada layar.
             */
            ->navigationGroups([
                NavigationGroup::make('Konten & Informasi')
                    ->icon('heroicon-o-megaphone')
                    ->collapsible(),
                NavigationGroup::make('Media & Pustaka')
                    ->icon('heroicon-o-photo')
                    ->collapsible(),
                NavigationGroup::make('Layanan Jamaah')
                    ->icon('heroicon-o-hand-raised')
                    ->collapsible(),
                NavigationGroup::make('Keuangan')
                    ->icon('heroicon-o-banknotes')
                    ->collapsible(),
                NavigationGroup::make('Profil Masjid')
                    ->icon('heroicon-o-building-library')
                    ->collapsible(),
                NavigationGroup::make('Sistem')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsible(),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                /*
                 * Panel Filament mendaftarkan tumpukan middleware-nya sendiri
                 * dan tidak memakai grup `web`, jadi header keamanan yang
                 * dipasang di bootstrap/app.php tidak sampai ke sini. Tanpa
                 * baris ini admin panel tetap tanpa perlindungan clickjacking —
                 * justru di bagian yang memegang seluruh data masjid.
                 *
                 * Parameter `admin` memilih CSP yang dilonggarkan untuk Alpine
                 * dan Livewire; halaman publik tetap memakai kebijakan ketat.
                 */
                SecurityHeaders::class.':admin',
            ])
            ->plugins([
                /*
                 * Shield menaruh Peran di grup "Filament Shield" miliknya
                 * sendiri — nama teknis yang tidak berarti bagi Tim DKM, dan
                 * menyisakan satu grup berisi satu menu. Dipindahkan ke Sistem,
                 * berdampingan dengan Pengguna dan Log Aktivitas.
                 *
                 * Ikonnya juga dilepas — termasuk ikon versi aktif, yang ikut
                 * diperiksa Filament saat melarang ikon di dua tingkat.
                 */
                FilamentShieldPlugin::make()
                    ->navigationGroup('Sistem')
                    ->navigationLabel('Peran & Hak Akses')
                    ->navigationSort(2)
                    ->navigationIcon(null)
                    ->activeNavigationIcon(null),
                FilamentFullCalendarPlugin::make()
                    ->selectable(false)
                    ->editable(false)
                    ->timezone(config('app.timezone'))
                    ->locale('id'),
            ])
            /*
             * Sidebar akordion: hanya satu grup terbuka pada satu waktu.
             */
            ->renderHook(
                PanelsRenderHook::SCRIPTS_AFTER,
                fn (): string => view('filament.sidebar-accordion')->render(),
            )
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
