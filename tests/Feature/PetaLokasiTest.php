<?php

use App\Models\MosqueSetting;

beforeEach(function (): void {
    seedMasterData();
});

it('menolak link berbagi Google Maps yang tidak bisa disematkan', function (string $url): void {
    // Google menolak alamat-alamat ini dimuat di dalam iframe lewat header
    // X-Frame-Options, dan penolakannya tidak bersuara — pengunjung hanya
    // melihat kotak kosong tanpa pesan apa pun.
    expect(MosqueSetting::isEmbeddableMapsUrl($url))->toBeFalse();
})->with([
    'link berbagi pendek' => 'https://maps.app.goo.gl/KpiWQydbsuCubJ9QA',
    'goo.gl lama' => 'https://goo.gl/maps/abc123',
    'hasil pencarian' => 'https://www.google.com/maps/search/-6.19,+106.63',
    'halaman tempat' => 'https://www.google.com/maps/place/Masjid+An-Nur',
    'domain lain' => 'https://contoh.test/maps/embed?pb=123',
]);

it('menerima bentuk URL yang memang bisa disematkan', function (string $url): void {
    expect(MosqueSetting::isEmbeddableMapsUrl($url))->toBeTrue();
})->with([
    'URL sematan resmi' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3',
    'peta biasa + output=embed' => 'https://maps.google.com/maps?q=-6.17,106.63&output=embed',
]);

it('tetap menampilkan peta dari koordinat saat URL sematan salah', function (): void {
    // Perbedaan "link berbagi" dan "URL sematan" bukan hal yang wajar dipahami
    // pengurus masjid. Koordinat sudah tersimpan untuk perhitungan jadwal
    // sholat, jadi peta tidak perlu bergantung pada pemahaman itu.
    $setting = MosqueSetting::current();
    $setting->forceFill([
        'maps_embed_url' => 'https://maps.app.goo.gl/KpiWQydbsuCubJ9QA',
        'latitude' => -6.178306,
        'longitude' => 106.631417,
    ])->save();

    $src = $setting->fresh()->mapsEmbedSrc();

    expect($src)->toContain('output=embed')
        ->and($src)->toContain('-6.178306')
        ->and($src)->not->toContain('maps.app.goo.gl');
});

it('mendahulukan URL sematan yang diisi benar', function (): void {
    $sematan = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966';

    $setting = MosqueSetting::current();
    $setting->forceFill(['maps_embed_url' => $sematan])->save();

    expect($setting->fresh()->mapsEmbedSrc())->toBe($sematan);
});

it('tidak memasang iframe bila koordinat dan URL sama-sama kosong', function (): void {
    $setting = MosqueSetting::current();
    $setting->forceFill([
        'maps_embed_url' => null,
        'latitude' => null,
        'longitude' => null,
    ])->save();

    expect($setting->fresh()->mapsEmbedSrc())->toBeNull();
});

it('merender peta di halaman kontak, bukan kotak kosong', function (): void {
    $setting = MosqueSetting::current();
    $setting->forceFill([
        'maps_embed_url' => 'https://maps.app.goo.gl/KpiWQydbsuCubJ9QA',
        'latitude' => -6.178306,
        'longitude' => 106.631417,
    ])->save();

    $this->get('/kontak')
        ->assertSuccessful()
        ->assertSee('output=embed', escape: false)
        ->assertDontSee('maps.app.goo.gl', escape: false);
});
