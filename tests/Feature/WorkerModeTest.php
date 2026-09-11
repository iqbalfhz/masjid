<?php

use App\View\Composers\PublicLayoutComposer;

beforeEach(function (): void {
    seedMasterData();
});

/**
 * Baca navigasi yang sudah dimemoize di dalam composer.
 */
function menuAktif(): ?string
{
    $composer = app(PublicLayoutComposer::class);

    $property = new ReflectionProperty($composer, 'navigation');
    $navigation = $property->getValue($composer) ?? [];

    return collect($navigation)->firstWhere('active', true)['label'] ?? null;
}

it('tidak membocorkan state antar-request saat berjalan di worker mode', function (): void {
    // FrankenPHP/Octane worker mode menahan aplikasi di memori antar-request,
    // sehingga binding `singleton` ikut bertahan. PublicLayoutComposer memoize
    // `active` yang berasal dari request()->routeIs() — state milik satu
    // request. Bila binding-nya singleton, menu akan tersorot mengikuti halaman
    // yang dibuka pengunjung pertama, dan pengaturan masjid membeku sampai
    // worker di-restart.
    //
    // forgetScopedInstances() adalah persis yang dijalankan Octane di antara
    // dua request: binding `scoped` dibuang, `singleton` tidak.
    $this->get('/kajian')->assertSuccessful();
    $pertama = app(PublicLayoutComposer::class);

    expect(menuAktif())->toBe('Kajian');

    app()->forgetScopedInstances();

    $this->get('/faq')->assertSuccessful();

    expect(app(PublicLayoutComposer::class))->not->toBe($pertama)
        ->and(menuAktif())->toBe('FAQ');
});
