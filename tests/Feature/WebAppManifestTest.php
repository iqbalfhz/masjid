<?php

use App\Models\MosqueSetting;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    seedMasterData();
});

it('menyajikan manifest yang membuat situs bisa dipasang ke Layar Utama', function (): void {
    // Di iPhone manifest ini bukan pemanis: Safari hanya membuka Web Push untuk
    // situs yang dipasang sebagai web app, dan itu mensyaratkan display
    // standalone. Tanpa berkas ini pengingat sholat mustahil dari iPhone.
    $this->get('/site.webmanifest')
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'application/manifest+json')
        ->assertJsonPath('display', 'standalone')
        ->assertJsonPath('start_url', '/')
        ->assertJsonPath('name', MosqueSetting::current()->name);
});

it('menautkan manifest dan ikon Layar Utama di halaman publik', function (): void {
    $this->get('/')
        ->assertSuccessful()
        ->assertSee('rel="manifest"', false)
        ->assertSee('rel="apple-touch-icon"', false);
});

it('hanya menunjuk berkas ikon yang benar-benar ada', function (): void {
    // Ikon notifikasi versi sebelumnya menunjuk berkas yang tidak pernah ada,
    // dan kegagalannya tidak terlihat: browser diam-diam memakai ikon bawaannya.
    $sumber = collect($this->get('/site.webmanifest')->json('icons'))->pluck('src');

    expect($sumber)->not->toBeEmpty();

    $sumber->each(fn (string $src) => expect(public_path((string) parse_url($src, PHP_URL_PATH)))->toBeFile());
});

it('hanya memakai ikon yang ada di service worker', function (): void {
    preg_match_all("#'(/images/[^']+)'#", (string) file_get_contents(public_path('sw.js')), $cocok);

    expect($cocok[1])->not->toBeEmpty();

    foreach ($cocok[1] as $path) {
        expect(public_path($path))->toBeFile();
    }
});

it('memakai logo masjid sebagai ikon begitu pengurus mengunggahnya', function (): void {
    MosqueSetting::current()->update(['logo' => 'identitas/logo.png']);

    $this->get('/site.webmanifest')
        ->assertJsonPath('icons.0.src', Storage::url('identitas/logo.png'));
});
