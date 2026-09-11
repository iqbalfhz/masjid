<?php

use App\Enums\ContentStatus;
use App\Models\Event;
use App\Models\Study;

beforeEach(function (): void {
    seedMasterData();
});

/**
 * Deskripsi kegiatan dan kajian diisi lewat Textarea di admin panel — teks
 * biasa, bukan HTML. Merendernya tanpa escape membuat apa pun yang diketik
 * tersimpan sebagai HTML aktif di halaman publik.
 *
 * Berbeda dengan Artikel, Pengumuman, dan FAQ yang memang memakai RichEditor
 * dan sengaja menyimpan HTML.
 */
$skrip = '<script>alert("xss")</script>';

it('meng-escape deskripsi kegiatan yang berisi tag HTML', function () use ($skrip): void {
    $event = Event::factory()->create([
        'title' => 'Kerja Bakti',
        'description' => "Bawa alat kebersihan. {$skrip}",
        'status' => ContentStatus::Disetujui,
    ]);

    $this->get(route('kegiatan.show', $event))
        ->assertSuccessful()
        ->assertDontSee($skrip, escape: false)
        ->assertSee('Bawa alat kebersihan');
});

it('meng-escape deskripsi kajian yang berisi tag HTML', function () use ($skrip): void {
    $study = Study::factory()->create([
        'theme' => 'Tafsir Pagi',
        'description' => "Terbuka untuk umum. {$skrip}",
        'status' => ContentStatus::Disetujui,
    ]);

    $this->get(route('kajian.show', $study))
        ->assertSuccessful()
        ->assertDontSee($skrip, escape: false)
        ->assertSee('Terbuka untuk umum');
});
