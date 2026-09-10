<?php

namespace App\Providers\Filament;

use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
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
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
                FilamentFullCalendarPlugin::make()
                    ->selectable(false)
                    ->editable(false)
                    ->timezone(config('app.timezone'))
                    ->locale('id'),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
