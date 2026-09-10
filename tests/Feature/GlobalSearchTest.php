<?php

use App\Models\Article;
use App\Models\Event;
use App\Models\Faq;
use App\Models\Study;
use App\Services\GlobalSearchService;

beforeEach(function (): void {
    seedMasterData();
});

it('mengelompokkan hasil pencarian per jenis konten', function (): void {
    Study::factory()->approved()->create(['theme' => 'Kajian Ramadhan Penuh Berkah']);
    Article::factory()->approved()->create(['title' => 'Persiapan Menyambut Ramadhan']);
    Faq::factory()->create(['question' => 'Kapan tarawih Ramadhan dimulai?']);

    $groups = app(GlobalSearchService::class)->search('Ramadhan');

    expect($groups->pluck('type')->all())
        ->toContain('kajian')
        ->toContain('artikel')
        ->toContain('faq');
});

it('mengembalikan hasil kosong untuk kata kunci kosong', function (): void {
    Study::factory()->approved()->create(['theme' => 'Kajian Tafsir']);

    expect(app(GlobalSearchService::class)->search('   '))->toBeEmpty();
});

it('tidak memunculkan konten yang belum disetujui di hasil pencarian', function (): void {
    Study::factory()->create(['theme' => 'Kajian Rahasia Draft']);
    Event::factory()->create(['title' => 'Kegiatan Rahasia Draft']);

    $groups = app(GlobalSearchService::class)->search('Rahasia');

    expect($groups)->toBeEmpty();
});

it('tidak memunculkan artikel yang tanggal tayangnya belum tiba', function (): void {
    Article::factory()->scheduled()->create(['title' => 'Artikel Terjadwal Nanti']);

    expect(app(GlobalSearchService::class)->search('Terjadwal'))->toBeEmpty();
});

it('menyembunyikan FAQ yang tidak dipublikasikan dari pencarian', function (): void {
    Faq::factory()->unpublished()->create(['question' => 'Pertanyaan Tersembunyi Sekali?']);

    expect(app(GlobalSearchService::class)->search('Tersembunyi'))->toBeEmpty();
});

it('menampilkan halaman hasil pencarian dengan jumlah temuan', function (): void {
    Study::factory()->approved()->create(['theme' => 'Kajian Zakat Fitrah']);

    $this->get('/cari?q=Zakat')
        ->assertSuccessful()
        ->assertSee('Kajian Zakat Fitrah')
        ->assertSee('hasil untuk');
});

it('memberi tahu ketika pencarian tidak menemukan apa pun', function (): void {
    $this->get('/cari?q=katakuncitidakada')
        ->assertSuccessful()
        ->assertSee('Tidak ditemukan hasil');
});

it('menjelajahi konten lintas modul lewat halaman tag', function (): void {
    $study = Study::factory()->approved()->create(['theme' => 'Kajian Anak Saleh']);
    $article = Article::factory()->approved()->create(['title' => 'Mendidik Anak di Era Digital']);

    $study->attachTags(['Anak & Remaja']);
    $article->attachTags(['Anak & Remaja']);

    $tag = $study->tags()->firstOrFail();

    $this->get(route('tag.show', $tag))
        ->assertSuccessful()
        ->assertSee($study->theme)
        ->assertSee($article->title);
});
