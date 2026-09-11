<?php

use App\Enums\UserRole;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;

beforeEach(function (): void {
    seedMasterData();
});

/**
 * @return array<string, list<string>>
 */
function grupNavigasi(): array
{
    return collect(Filament::getPanel('admin')->getNavigation())
        ->mapWithKeys(fn (NavigationGroup $group): array => [
            $group->getLabel() ?? 'Tanpa Grup' => collect($group->getItems())
                ->map(fn ($item): string => $item->getLabel())
                ->all(),
        ])
        ->all();
}

it('menyusun grup sidebar dari yang paling sering dipakai ke administratif', function (): void {
    $this->actingAs(userWithRole(UserRole::Superadmin));

    $urutan = collect(array_keys(grupNavigasi()))
        ->reject(fn (string $label): bool => $label === 'Tanpa Grup')
        ->values()
        ->all();

    expect($urutan)->toBe([
        'Konten & Informasi',
        'Media & Pustaka',
        'Layanan Jamaah',
        'Keuangan',
        'Profil Masjid',
        'Sistem',
    ]);
});

it('tidak menyisakan grup yang isinya cuma satu menu', function (): void {
    // Shield semula menaruh Peran di grup "Filament Shield" miliknya sendiri —
    // nama teknis yang tak berarti bagi Tim DKM, dan memakan satu baris grup
    // penuh hanya untuk satu menu.
    $this->actingAs(userWithRole(UserRole::Superadmin));

    $grup = collect(grupNavigasi())->forget('Tanpa Grup');

    expect($grup)->not->toHaveKey('Filament Shield');

    $grup->each(fn (array $menu, string $label) => expect($menu)
        ->toHaveCount(count($menu))
        ->and(count($menu))->toBeGreaterThan(1, "Grup [{$label}] hanya berisi satu menu"));
});

it('menyeimbangkan jumlah menu tiap grup', function (): void {
    // Sebelumnya "Konten & Informasi" menampung 9 menu sementara "Keuangan"
    // hanya 2 — sidebar jadi timpang dan panjang sebelah.
    $this->actingAs(userWithRole(UserRole::Superadmin));

    $jumlah = collect(grupNavigasi())->forget('Tanpa Grup')->map(fn (array $m): int => count($m));

    expect($jumlah->max())->toBeLessThanOrEqual(6)
        ->and($jumlah['Konten & Informasi'])->toBe(4)
        ->and($jumlah['Media & Pustaka'])->toBe(5)
        ->and($jumlah['Sistem'])->toBe(3);
});

it('membuat semua grup bisa dilipat agar akordion berlaku', function (): void {
    $this->actingAs(userWithRole(UserRole::Superadmin));

    collect(Filament::getPanel('admin')->getNavigation())
        ->reject(fn (NavigationGroup $g): bool => $g->getLabel() === null)
        ->each(fn (NavigationGroup $g) => expect($g->isCollapsible())
            ->toBeTrue("Grup [{$g->getLabel()}] tidak bisa dilipat"));
});

it('memuat skrip akordion di panel admin', function (): void {
    $this->actingAs(userWithRole(UserRole::KetuaDkm))
        ->get('/admin')
        ->assertSuccessful()
        ->assertSee('toggleCollapsedGroup', escape: false)
        ->assertSee('livewire:navigated', escape: false);
});

it('memindahkan ikon dari tiap menu ke grupnya', function (): void {
    // Filament hanya mengizinkan ikon di salah satu tingkat. Menaruhnya di grup
    // membuat menu di dalamnya bisa dirender sebagai titik bertali, sehingga
    // hierarkinya terbaca sekilas — pola yang dipakai NADI.
    $this->actingAs(userWithRole(UserRole::Superadmin));

    collect(Filament::getPanel('admin')->getNavigation())
        ->reject(fn (NavigationGroup $g): bool => $g->getLabel() === null)
        ->each(function (NavigationGroup $g): void {
            expect($g->getIcon())->not->toBeNull("Grup [{$g->getLabel()}] kehilangan ikon");

            foreach ($g->getItems() as $item) {
                // Ikon versi aktif ikut diperiksa Filament saat melarang ikon di
                // dua tingkat, jadi ikut diuji di sini. Plugin Shield memasang
                // ikon aktif pada Peran & Hak Akses lewat default-nya, dan itu
                // sempat lolos karena pengujian awal hanya melihat getIcon().
                expect($item->getIcon())->toBeNull(
                    "Menu [{$item->getLabel()}] masih berikon; Filament akan menolak render grup berikon",
                );

                expect($item->getActiveIcon())->toBeNull(
                    "Menu [{$item->getLabel()}] masih punya ikon aktif; Filament ikut memeriksanya",
                );
            }
        });
});

it('merender titik bertali bawaan Filament di bawah ikon grup', function (): void {
    // Penanda hierarki ini tidak perlu CSS sendiri: Filament merendernya begitu
    // menu di dalam grup berikon tidak lagi punya ikon masing-masing. Sempat
    // ditambahkan versi buatan sendiri, dan hasilnya dua garis bertumpuk.
    $this->actingAs(userWithRole(UserRole::KetuaDkm))
        ->get('/admin')
        ->assertSuccessful()
        ->assertSee('fi-sidebar-item-grouped-border', escape: false);
});
