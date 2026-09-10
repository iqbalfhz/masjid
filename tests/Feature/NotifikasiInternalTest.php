<?php

use App\Enums\UserRole;
use App\Models\Announcement;
use App\Models\Facility;
use App\Services\AdminNotifier;
use Filament\Facades\Filament;
use Filament\Livewire\DatabaseNotifications;
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
