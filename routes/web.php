<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FacilityBookingController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\FinanceReportController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\MosqueProfileController;
use App\Http\Controllers\PrayerScheduleController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\QurbanRegistrationController;
use App\Http\Controllers\RsvpController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\StudyController;
use App\Http\Controllers\SuggestionController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\ZakatRegistrationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Website Publik
|--------------------------------------------------------------------------
|
| Seluruh halaman yang diakses jamaah. Admin panel ditangani oleh Filament
| pada prefix /admin (lihat AdminPanelProvider).
|
*/

Route::get('/', HomeController::class)->name('home');

Route::get('/jadwal-sholat', PrayerScheduleController::class)->name('jadwal-sholat');

Route::get('/pengumuman/{announcement:slug}', AnnouncementController::class)->name('pengumuman.show');

Route::get('/kajian', [StudyController::class, 'index'])->name('kajian.index');
Route::get('/kajian/{study:slug}', [StudyController::class, 'show'])->name('kajian.show');

Route::get('/kegiatan', [EventController::class, 'index'])->name('kegiatan.index');
Route::get('/kegiatan/{event:slug}', [EventController::class, 'show'])->name('kegiatan.show');

Route::get('/laporan-keuangan', [FinanceReportController::class, 'index'])->name('laporan-keuangan');
Route::get('/laporan-keuangan/unduh', [FinanceReportController::class, 'download'])->name('laporan-keuangan.unduh');

Route::get('/donasi', DonationController::class)->name('donasi');

Route::get('/profil', MosqueProfileController::class)->name('profil');

Route::get('/galeri', [GalleryController::class, 'index'])->name('galeri.index');
Route::get('/galeri/{album:slug}', [GalleryController::class, 'show'])->name('galeri.show');

Route::get('/artikel', [ArticleController::class, 'index'])->name('artikel.index');
Route::get('/artikel/{article:slug}', [ArticleController::class, 'show'])->name('artikel.show');

Route::get('/e-library', LibraryController::class)->name('e-library');
Route::get('/e-library/{material:slug}/unduh', [LibraryController::class, 'download'])->name('e-library.unduh');

Route::get('/faq', FaqController::class)->name('faq');
Route::get('/kontak', ContactController::class)->name('kontak');
Route::get('/cari', SearchController::class)->name('cari');
Route::get('/tag/{tag}', TagController::class)->name('tag.show');

Route::get('/testimoni', [TestimonialController::class, 'index'])->name('testimoni.index');
Route::get('/kotak-saran', [SuggestionController::class, 'create'])->name('saran.create');
Route::get('/layanan/kurban', [QurbanRegistrationController::class, 'create'])->name('kurban.create');
Route::get('/layanan/zakat', [ZakatRegistrationController::class, 'create'])->name('zakat.create');
Route::get('/peminjaman-fasilitas', [FacilityBookingController::class, 'create'])->name('fasilitas.create');
Route::get('/peminjaman-fasilitas/kalender', [FacilityBookingController::class, 'calendar'])->name('fasilitas.kalender');
Route::get('/peminjaman-fasilitas/status', [FacilityBookingController::class, 'status'])->name('fasilitas.status');

/*
|--------------------------------------------------------------------------
| Form Jamaah
|--------------------------------------------------------------------------
|
| Semua submit dari jamaah dibatasi rate limit agar tidak dibanjiri spam
| (PRD bagian 6). Proteksi honeypot ada di masing-masing Form Request.
|
*/

Route::middleware('throttle:public-forms')->group(function (): void {
    Route::post('/testimoni', [TestimonialController::class, 'store'])->name('testimoni.store');
    Route::post('/kotak-saran', [SuggestionController::class, 'store'])->name('saran.store');
    Route::post('/layanan/kurban', [QurbanRegistrationController::class, 'store'])->name('kurban.store');
    Route::post('/layanan/zakat', [ZakatRegistrationController::class, 'store'])->name('zakat.store');
    Route::post('/peminjaman-fasilitas', [FacilityBookingController::class, 'store'])->name('fasilitas.store');
    Route::post('/rsvp', RsvpController::class)->name('rsvp.store');
});

/*
|--------------------------------------------------------------------------
| Web Push (Reminder Sholat)
|--------------------------------------------------------------------------
*/

Route::prefix('push')->name('push.')->group(function (): void {
    Route::get('/kunci-publik', [PushSubscriptionController::class, 'publicKey'])->name('public-key');
    Route::post('/langganan', [PushSubscriptionController::class, 'store'])->middleware('throttle:public-forms')->name('subscribe');
    Route::delete('/langganan', [PushSubscriptionController::class, 'destroy'])->name('unsubscribe');
    Route::get('/konten', [PushSubscriptionController::class, 'content'])->name('content');
});
