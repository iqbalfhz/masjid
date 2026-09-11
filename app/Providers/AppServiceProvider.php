<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Listeners\RefreshNavigationBadges;
use App\Models\User;
use App\Policies\ActivityPolicy;
use App\View\Composers\PublicLayoutComposer;
use Carbon\CarbonImmutable;
use Filament\Actions\Events\ActionCalled;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /**
         * `scoped`, bukan `singleton`.
         *
         * Composer ini memoize hasil query sekaligus penanda menu aktif, dan
         * penanda itu berasal dari request yang sedang berjalan. Di mode klasik
         * keduanya setara karena container dibangun ulang tiap request, tapi di
         * worker mode (FrankenPHP/Octane) aplikasi bertahan di memori: binding
         * `singleton` akan menyajikan menu yang tersorot mengikuti halaman
         * pengunjung pertama, dan pengaturan masjid yang membeku sampai worker
         * di-restart. Binding `scoped` dibuang di antara request.
         */
        $this->app->scoped(PublicLayoutComposer::class);
    }

    public function boot(): void
    {
        Carbon::setLocale(config('app.locale'));
        CarbonImmutable::setLocale(config('app.locale'));

        Model::preventLazyLoading(! $this->app->isProduction());
        Model::preventSilentlyDiscardingAttributes($this->app->isLocal());

        /**
         * Superadmin adalah pemilik teknis sistem: ia lolos seluruh pengecekan
         * permission tanpa perlu di-assign satu per satu (PRD bagian 3 & 5.3).
         */
        Gate::before(fn (User $user): ?bool => $user->hasRole(UserRole::Superadmin->value) ? true : null);

        /**
         * Model Activity milik vendor berada di luar jangkauan penemuan policy
         * otomatis Laravel, jadi didaftarkan manual.
         */
        Gate::policy(Activity::class, ActivityPolicy::class);

        View::composer(['layouts.public', 'public.*'], PublicLayoutComposer::class);

        /**
         * Badge navigasi hidup di komponen Livewire sidebar yang terpisah dari
         * komponen halaman, jadi aksi tabel tidak ikut menyegarkannya.
         *
         * Didaftarkan manual karena Filament mengirim nama event dengan Action
         * sebagai payload, sehingga penemuan otomatis Laravel salah menyimpulkan
         * nama event-nya dari type-hint listener.
         */
        Event::listen(ActionCalled::class, RefreshNavigationBadges::class);

        $this->configureRateLimiting();
    }

    /**
     * Batasi submit form publik (testimoni, saran, RSVP, pendaftaran layanan)
     * agar tidak dibanjiri spam — PRD bagian 6.
     */
    private function configureRateLimiting(): void
    {
        [$attempts, $minutes] = array_pad(explode(',', (string) config('masjid.forms.rate_limit')), 2, 1);

        RateLimiter::for('public-forms', fn (Request $request) => Limit::perMinutes(
            (int) $minutes,
            (int) $attempts,
        )->by($request->ip()));
    }
}
