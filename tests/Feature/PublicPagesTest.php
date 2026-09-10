<?php

use App\Models\Announcement;
use App\Models\Article;
use App\Models\Event;
use App\Models\Facility;
use App\Models\FacilityBooking;
use App\Models\Faq;
use App\Models\FinanceTransaction;
use App\Models\GalleryAlbum;
use App\Models\GalleryItem;
use App\Models\LibraryMaterial;
use App\Models\PrayerSchedule;
use App\Models\Study;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    seedMasterData();
});

it('membuka halaman statis tanpa error', function (string $path): void {
    $this->get($path)->assertSuccessful();
})->with([
    '/',
    '/jadwal-sholat',
    '/kajian',
    '/kegiatan',
    '/laporan-keuangan',
    '/donasi',
    '/profil',
    '/galeri',
    '/artikel',
    '/e-library',
    '/faq',
    '/kontak',
    '/cari',
    '/testimoni',
    '/kotak-saran',
    '/layanan/kurban',
    '/layanan/zakat',
    '/peminjaman-fasilitas',
    '/peminjaman-fasilitas/status',
]);

it('menampilkan jadwal sholat hari ini di beranda', function (): void {
    PrayerSchedule::factory()->create(['date' => today()]);

    $this->get('/')
        ->assertSuccessful()
        ->assertSee('Jadwal sholat hari ini')
        ->assertSee('04:30');
});

it('hanya menampilkan pengumuman yang sudah disetujui dan masih berlaku', function (): void {
    $tayang = Announcement::factory()->approved()->create(['title' => 'Pengumuman Tayang']);
    Announcement::factory()->awaitingApproval()->create(['title' => 'Pengumuman Menunggu']);
    Announcement::factory()->approved()->expired()->create(['title' => 'Pengumuman Kedaluwarsa']);

    $this->get('/')
        ->assertSee($tayang->title)
        ->assertDontSee('Pengumuman Menunggu')
        ->assertDontSee('Pengumuman Kedaluwarsa');
});

it('menolak akses ke pengumuman yang belum disetujui', function (): void {
    $announcement = Announcement::factory()->awaitingApproval()->create();

    $this->get(route('pengumuman.show', $announcement))->assertNotFound();
});

it('hanya menampilkan kajian yang sudah disetujui', function (): void {
    $tayang = Study::factory()->approved()->create(['theme' => 'Kajian Tayang']);
    Study::factory()->create(['theme' => 'Kajian Draft']);

    $this->get('/kajian')
        ->assertSee($tayang->theme)
        ->assertDontSee('Kajian Draft');
});

it('menolak akses ke detail kajian yang masih draft', function (): void {
    $study = Study::factory()->create();

    $this->get(route('kajian.show', $study))->assertNotFound();
});

it('menyembunyikan artikel yang tanggal tayangnya belum tiba', function (): void {
    $tayang = Article::factory()->approved()->create(['title' => 'Artikel Tayang']);
    Article::factory()->scheduled()->create(['title' => 'Artikel Terjadwal']);

    $this->get('/artikel')
        ->assertSee($tayang->title)
        ->assertDontSee('Artikel Terjadwal');
});

it('menolak akses ke artikel yang belum waktunya tayang', function (): void {
    $article = Article::factory()->scheduled()->create();

    $this->get(route('artikel.show', $article))->assertNotFound();
});

it('menambah jumlah pembaca ketika artikel dibuka', function (): void {
    $article = Article::factory()->approved()->create(['views' => 0]);

    $this->get(route('artikel.show', $article))->assertSuccessful();

    expect($article->fresh()->views)->toBe(1);
});

it('memisahkan kegiatan akan datang dari arsip', function (): void {
    $mendatang = Event::factory()->approved()->create(['title' => 'Kegiatan Mendatang']);
    $lampau = Event::factory()->approved()->past()->create(['title' => 'Kegiatan Lampau']);

    $this->get('/kegiatan')
        ->assertSee($mendatang->title)
        ->assertDontSee($lampau->title);

    $this->get('/kegiatan?arsip=1')
        ->assertSee($lampau->title)
        ->assertDontSee($mendatang->title);
});

it('menampilkan ringkasan keuangan sesuai filter', function (): void {
    FinanceTransaction::factory()->income()->create(['amount' => 1_000_000, 'date' => today()]);
    FinanceTransaction::factory()->expense()->create(['amount' => 250_000, 'date' => today()]);

    $this->get('/laporan-keuangan')
        ->assertSuccessful()
        ->assertSee('Rp 1.000.000')
        ->assertSee('Rp 250.000')
        ->assertSee('Rp 750.000');
});

it('menghasilkan unduhan PDF laporan keuangan', function (): void {
    FinanceTransaction::factory()->income()->create(['amount' => 500_000, 'date' => today()]);

    $response = $this->get('/laporan-keuangan/unduh');

    $response->assertSuccessful();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

it('menolak filter tanggal yang terbalik pada laporan keuangan', function (): void {
    $this->get('/laporan-keuangan?dari=2026-05-01&sampai=2026-01-01')
        ->assertSessionHasErrors('sampai');
});

it('hanya menampilkan testimoni yang sudah dimoderasi', function (): void {
    $tayang = Testimonial::factory()->approved()->create(['message' => 'Testimoni yang disetujui pengurus']);
    Testimonial::factory()->create(['message' => 'Testimoni menunggu moderasi']);

    $this->get('/testimoni')
        ->assertSee($tayang->message)
        ->assertDontSee('Testimoni menunggu moderasi');
});

it('hanya menampilkan album galeri yang dipublikasikan', function (): void {
    $tayang = GalleryAlbum::factory()->create(['title' => 'Album Tayang']);
    GalleryItem::factory()->create(['gallery_album_id' => $tayang->id]);
    GalleryAlbum::factory()->unpublished()->create(['title' => 'Album Tersembunyi']);

    $this->get('/galeri')
        ->assertSee($tayang->title)
        ->assertDontSee('Album Tersembunyi');
});

it('menolak akses ke album galeri yang belum dipublikasikan', function (): void {
    $album = GalleryAlbum::factory()->unpublished()->create();

    $this->get(route('galeri.show', $album))->assertNotFound();
});

it('menyembunyikan FAQ yang tidak dipublikasikan', function (): void {
    $tayang = Faq::factory()->create(['question' => 'Pertanyaan yang tayang?']);
    Faq::factory()->unpublished()->create(['question' => 'Pertanyaan tersembunyi?']);

    $this->get('/faq')
        ->assertSee($tayang->question)
        ->assertDontSee('Pertanyaan tersembunyi?');
});

it('menghitung akses materi e-library lalu mengarahkan ke berkasnya', function (): void {
    $material = LibraryMaterial::factory()->video()->create(['downloads' => 0]);

    $this->get(route('e-library.unduh', $material))
        ->assertRedirect($material->external_url);

    expect($material->fresh()->downloads)->toBe(1);
});

it('menampilkan status peminjaman berdasarkan nomor pengajuan', function (): void {
    $booking = FacilityBooking::factory()->approved()->create([
        'facility_id' => Facility::query()->firstOrFail()->id,
    ]);

    $this->get('/peminjaman-fasilitas/status?nomor='.$booking->booking_number)
        ->assertSuccessful()
        ->assertSee($booking->booking_number)
        ->assertSee($booking->purpose);
});

it('memberi tahu ketika nomor pengajuan tidak ditemukan', function (): void {
    $this->get('/peminjaman-fasilitas/status?nomor=PJF-TIDAK-ADA')
        ->assertSuccessful()
        ->assertSee('tidak ditemukan');
});

it('menyajikan berkas unggahan lewat URL relatif agar aman dari beda origin', function (): void {
    // URL absolut yang diturunkan dari APP_URL membuat pratinjau unggahan di
    // admin panel gagal dimuat ketika panel dibuka dari host lain.
    expect(Storage::url('donasi/qris.png'))->toBe('/storage/donasi/qris.png')
        ->and(config('filesystems.disks.public.url'))->toBe('/storage');
});

it('mengarahkan unduhan materi lokal ke host aplikasi', function (): void {
    $material = LibraryMaterial::factory()->create(['file_path' => 'e-library/materi.pdf']);

    $this->get(route('e-library.unduh', $material))
        ->assertRedirect(url('/storage/e-library/materi.pdf'));
});
