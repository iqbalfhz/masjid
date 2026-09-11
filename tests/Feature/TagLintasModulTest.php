<?php

use App\Enums\UserRole;
use App\Filament\Resources\Articles\Pages\EditArticle;
use App\Filament\Resources\GalleryAlbums\Pages\EditGalleryAlbum;
use App\Models\Article;
use App\Models\Event;
use App\Models\GalleryAlbum;
use App\Models\Study;
use Livewire\Livewire;

beforeEach(function (): void {
    seedMasterData();
});

/**
 * Tag lintas modul (PRD 5.1) disimpan lewat mutator `setTagsAttribute()` milik
 * spatie/laravel-tags. Mutator itu hanya terpanggil bila `tags` lolos
 * pemeriksaan isFillable() — dan selama tidak tercantum di #[Fillable],
 * Laravel menolaknya.
 *
 * Yang membuat ini mudah lolos dari perhatian: `preventSilentlyDiscardingAttributes()`
 * hanya aktif di environment lokal. Di lokal ia melempar MassAssignmentException
 * dengan jelas, sedangkan di produksi ia diam saja — tag tidak pernah tersimpan
 * dan tidak ada pesan apa pun.
 */
it('menyimpan tag saat model dibuat', function (string $model): void {
    // Lewat factory agar kolom wajib tiap model terisi sendiri; yang diuji di
    // sini jalur penyimpanan tag, bukan kelengkapan kolomnya.
    $record = $model::factory()->create([
        'tags' => ['Ramadhan', 'Anak & Remaja'],
    ]);

    expect($record->fresh()->tags->pluck('name')->all())
        ->toContain('Ramadhan')
        ->toContain('Anak & Remaja');
})->with([
    'galeri' => [GalleryAlbum::class],
    'artikel' => [Article::class],
    'kajian' => [Study::class],
    'kegiatan' => [Event::class],
]);

it('memperbarui tag pada model yang sudah ada', function (): void {
    $album = GalleryAlbum::factory()->create();
    $album->update(['tags' => ['Idul Fitri']]);

    expect($album->fresh()->tags->pluck('name')->all())->toBe(['Idul Fitri']);
});

it('menyimpan galeri beserta tag lewat admin panel', function (): void {
    // Regresi langsung: menyimpan album galeri dari form Filament sempat
    // melempar MassAssignmentException di layar pengurus.
    $album = GalleryAlbum::factory()->create(['title' => 'Santunan Anak Yatim']);

    Livewire::actingAs(userWithRole(UserRole::Sekretaris))
        ->test(EditGalleryAlbum::class, ['record' => $album->getRouteKey()])
        ->fillForm(['tags' => ['Bakti Sosial']])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($album->fresh()->tags->pluck('name')->all())->toContain('Bakti Sosial');
});

it('menyimpan artikel beserta tag lewat admin panel', function (): void {
    $article = Article::factory()->create();

    Livewire::actingAs(userWithRole(UserRole::Sekretaris))
        ->test(EditArticle::class, ['record' => $article->getRouteKey()])
        ->fillForm(['tags' => ['Kajian Rutin']])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($article->fresh()->tags->pluck('name')->all())->toContain('Kajian Rutin');
});
