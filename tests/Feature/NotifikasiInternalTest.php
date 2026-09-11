<?php

use App\Enums\UserRole;
use App\Filament\Exports\FinanceTransactionExporter;
use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\ApprovalQueueWidget;
use App\Filament\Widgets\MosqueOverviewWidget;
use App\Models\Announcement;
use App\Models\Facility;
use App\Services\AdminNotifier;
use Filament\Actions\Exports\Models\Export;
use Filament\Facades\Filament;
use Filament\Livewire\DatabaseNotifications;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function (): void {
    seedMasterData();
});

it('mengaktifkan lonceng notifikasi di admin panel', function (): void {
    // Tanpa ini, seluruh notifikasi internal hanya menumpuk di tabel
    // `notifications` tanpa ada tempat pengurus membacanya (PRD 5.2.17).
    $panel = Filament::getPanel('admin');

    expect($panel->hasDatabaseNotifications())->toBeTrue()
        ->and($panel->getDatabaseNotificationsPollingInterval())->toBe('30s');
});

it('menampilkan notifikasi yang belum dibaca di lonceng', function (): void {
    $ketua = userWithRole(UserRole::KetuaDkm);
    $sekretaris = userWithRole(UserRole::Sekretaris);
    $announcement = Announcement::factory()->create([
        'title' => 'Kerja Bakti Akhir Pekan',
        'created_by' => $sekretaris->id,
    ]);

    app(AdminNotifier::class)->contentAwaitingApproval(
        $announcement,
        'pengumuman',
        $announcement->title,
        'http://localhost/admin',
    );

    $this->actingAs($ketua);

    // Daftar isinya dimuat lazy saat lonceng diklik, jadi yang tampil di render
    // awal adalah penanda jumlah belum dibaca.
    Livewire::test(DatabaseNotifications::class)
        ->assertSuccessful()
        ->assertSee('1 unread notification');

    $tersimpan = $ketua->fresh()->unreadNotifications()->firstOrFail();

    expect($tersimpan->data['title'])->toBe('Pengumuman menunggu approval')
        ->and($tersimpan->data['body'])->toContain('Kerja Bakti Akhir Pekan')
        ->and($tersimpan->data['actions'])->not->toBeEmpty();
});

it('tidak membocorkan notifikasi milik pengurus lain', function (): void {
    $ketua = userWithRole(UserRole::KetuaDkm);
    $bendahara = userWithRole(UserRole::Bendahara);

    app(AdminNotifier::class)->contentAwaitingApproval(
        Announcement::factory()->create(['title' => 'Rahasia Approver']),
        'pengumuman',
        'Rahasia Approver',
        'http://localhost/admin',
    );

    $this->actingAs($bendahara);

    Livewire::test(DatabaseNotifications::class)
        ->assertSuccessful()
        ->assertDontSee('unread notification');

    expect($ketua->fresh()->unreadNotifications()->count())->toBe(1)
        ->and($bendahara->fresh()->unreadNotifications()->count())->toBe(0);
});

it('mengantar notifikasi pendaftaran layanan ke bendahara', function (): void {
    $bendahara = userWithRole(UserRole::Bendahara);

    app(AdminNotifier::class)->newRegistration(
        'zakat',
        'Pendaftaran zakat baru',
        'Bu Aminah mendaftar Zakat Fitrah.',
        'http://localhost/admin',
    );

    $this->actingAs($bendahara);

    Livewire::test(DatabaseNotifications::class)
        ->assertSuccessful()
        ->assertSee('1 unread notification');

    expect($bendahara->fresh()->unreadNotifications()->firstOrFail()->data['title'])
        ->toBe('Pendaftaran zakat baru');
});

it('mengantar masukan jamaah ke sekretaris', function (): void {
    $sekretaris = userWithRole(UserRole::Sekretaris);

    app(AdminNotifier::class)->newJamaahInput(
        'Testimoni baru menunggu moderasi',
        'Masjidnya bersih dan nyaman.',
        'http://localhost/admin',
    );

    $this->actingAs($sekretaris);

    Livewire::test(DatabaseNotifications::class)
        ->assertSuccessful()
        ->assertSee('1 unread notification');

    expect($sekretaris->fresh()->unreadNotifications()->firstOrFail()->data['title'])
        ->toBe('Testimoni baru menunggu moderasi');
});

it('mengantar pengajuan fasilitas ke approver dan sekretaris', function (): void {
    $ketua = userWithRole(UserRole::KetuaDkm);
    $sekretaris = userWithRole(UserRole::Sekretaris);
    $bendahara = userWithRole(UserRole::Bendahara);

    app(AdminNotifier::class)->newFacilityBooking(
        'Pengajuan peminjaman fasilitas baru',
        Facility::query()->firstOrFail()->name.' diajukan untuk akad nikah.',
        'http://localhost/admin',
    );

    expect($ketua->fresh()->unreadNotifications()->count())->toBe(1)
        ->and($sekretaris->fresh()->unreadNotifications()->count())->toBe(1)
        ->and($bendahara->fresh()->unreadNotifications()->count())->toBe(0);
});

it('menandai notifikasi terbaca setelah dibuka', function (): void {
    $ketua = userWithRole(UserRole::KetuaDkm);

    app(AdminNotifier::class)->contentAwaitingApproval(
        Announcement::factory()->create(),
        'pengumuman',
        'Perlu ditinjau',
        'http://localhost/admin',
    );

    $this->actingAs($ketua);

    expect($ketua->fresh()->unreadNotifications()->count())->toBe(1);

    Livewire::test(DatabaseNotifications::class)->call('markAllNotificationsAsRead');

    expect($ketua->fresh()->unreadNotifications()->count())->toBe(0);
});

it('menyediakan halaman profil agar pengurus bisa ganti password sendiri', function (): void {
    // PRD 7.1: tiap pengurus mengelola akunnya sendiri tanpa bantuan Superadmin.
    expect(Filament::getPanel('admin')->hasProfile())->toBeTrue();

    $this->actingAs(userWithRole(UserRole::Sekretaris))
        ->get(Filament::getPanel('admin')->getProfileUrl())
        ->assertSuccessful();
});

it('menampilkan hanya widget yang berguna bagi pengurus di dasbor', function (): void {
    // Kalender punya halaman sendiri, dan info versi framework tidak relevan
    // bagi Tim DKM.
    $widget = collect((new Dashboard)->getWidgets())->map(fn (string $w): string => class_basename($w));

    expect($widget)
        ->toContain('ApprovalQueueWidget')
        ->toContain('MosqueOverviewWidget')
        ->not->toContain('ActivityCalendarWidget')
        ->not->toContain('FilamentInfoWidget');
});

it('membuka dasbor beserta widget ringkasannya', function (): void {
    $this->actingAs(userWithRole(UserRole::KetuaDkm))
        ->get('/admin')
        ->assertSuccessful()
        ->assertSee('Dasbor');

    // Widget dirender sebagai komponen Livewire terpisah yang dimuat lazy,
    // jadi isinya diperiksa langsung ke komponennya.
    Livewire::test(ApprovalQueueWidget::class)
        ->assertSuccessful()
        ->assertSee('Menunggu approval');

    Livewire::test(MosqueOverviewWidget::class)
        ->assertSuccessful()
        ->assertSee('Pemasukan bulan ini');
});

it('mengantar notifikasi tanpa bergantung pada queue worker', function (): void {
    // Regresi PRD 5.2.17. `Filament\Notifications\DatabaseNotification`
    // mengimplementasikan ShouldQueue, sehingga `sendToDatabase()` melempar
    // notifikasi ke antrean. Server masjid umumnya tidak menjalankan
    // `queue:work`, jadi notifikasi menumpuk di tabel `jobs` dan lonceng
    // pengurus selamanya kosong.
    //
    // Seluruh suite memakai QUEUE_CONNECTION=sync sehingga bug ini tak
    // terlihat; di sini antrean sengaja dikembalikan ke `database` agar
    // kegagalan pengiriman benar-benar terdeteksi.
    config(['queue.default' => 'database']);

    $sekretaris = userWithRole(UserRole::Sekretaris);

    app(AdminNotifier::class)->newJamaahInput(
        'Kotak saran baru',
        'Mohon tambah kipas angin di saf belakang.',
        'http://localhost/admin',
    );

    expect(DB::table('jobs')->count())->toBe(0)
        ->and($sekretaris->fresh()->unreadNotifications()->count())->toBe(1);
});

it('mengantar salinan notifikasi export ke lonceng tanpa queue worker', function (): void {
    // Tautan unduhan hasil export hanya hidup di toast yang menghilang setelah
    // beberapa detik. Bila salinannya tersangkut di antrean, pengurus kehilangan
    // berkasnya begitu toast lewat.
    config(['queue.default' => 'database']);

    $bendahara = userWithRole(UserRole::Bendahara);

    $export = Export::query()->create([
        'user_id' => $bendahara->id,
        'exporter' => FinanceTransactionExporter::class,
        'file_disk' => 'public',
        'file_name' => 'rekap-keuangan',
        'total_rows' => 3,
        'processed_rows' => 3,
        'successful_rows' => 3,
        'completed_at' => now(),
    ]);

    FinanceTransactionExporter::modifyCompletedNotification(
        Notification::make()->title('Export selesai')->body('3 baris berhasil diexport.'),
        $export,
    );

    expect(DB::table('jobs')->count())->toBe(0)
        ->and($bendahara->fresh()->unreadNotifications()->firstOrFail()->data['title'])
        ->toBe('Export selesai');
});

it('menyimpan tautan notifikasi sebagai jalur relatif', function (): void {
    // Notifikasi bertahan permanen di basis data, sedangkan alamat absolut ikut
    // basi begitu origin berubah. URL yang dibekukan saat seeder berjalan lewat
    // CLI memakai APP_URL, sehingga tautannya mati ketika pengurus membuka panel
    // dari host atau port lain.
    $sekretaris = userWithRole(UserRole::Sekretaris);

    app(AdminNotifier::class)->newJamaahInput(
        'Kotak saran baru',
        'Mohon tambah rak sandal.',
        'http://localhost:8000/admin/suggestions?tab=baru',
    );

    $tautan = $sekretaris->fresh()->unreadNotifications()->firstOrFail()->data['actions'][0]['url'];

    expect($tautan)->toBe('/admin/suggestions?tab=baru')
        ->and($tautan)->not->toStartWith('http');
});
