<?php

use App\Enums\ContentStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Announcements\AnnouncementResource;
use App\Filament\Resources\Announcements\Pages\ListAnnouncements;
use App\Models\Announcement;
use Livewire\Livewire;

beforeEach(function (): void {
    seedMasterData();
});

it('menyegarkan badge navigasi setelah konten disetujui', function (): void {
    // Sidebar adalah komponen Livewire terpisah dari komponen halaman, jadi
    // menyetujui lewat aksi tabel tidak ikut merendernya. Tanpa pemicu ini
    // angka "menunggu approval" tetap memakai nilai lama sampai pengurus
    // memuat ulang halaman secara manual.
    $ketua = userWithRole(UserRole::KetuaDkm);

    $announcement = Announcement::factory()->create([
        'status' => ContentStatus::MenungguApproval,
    ]);

    expect(AnnouncementResource::getNavigationBadge())->toBe('1');

    Livewire::actingAs($ketua)
        ->test(ListAnnouncements::class)
        ->callTableAction('approve', $announcement, ['approval_note' => 'Sudah sesuai.'])
        ->assertHasNoTableActionErrors()
        ->assertDispatched('refresh-sidebar');

    expect(AnnouncementResource::getNavigationBadge())->toBeNull();
});

it('menyegarkan badge navigasi setelah konten ditolak', function (): void {
    $ketua = userWithRole(UserRole::KetuaDkm);

    $announcement = Announcement::factory()->create([
        'status' => ContentStatus::MenungguApproval,
    ]);

    Livewire::actingAs($ketua)
        ->test(ListAnnouncements::class)
        ->callTableAction('reject', $announcement, ['approval_note' => 'Mohon perbaiki tanggalnya.'])
        ->assertHasNoTableActionErrors()
        ->assertDispatched('refresh-sidebar');

    expect(AnnouncementResource::getNavigationBadge())->toBeNull();
});

it('menyegarkan badge navigasi setelah konten diajukan approval', function (): void {
    // Arah sebaliknya: badge harus muncul, bukan hilang.
    $sekretaris = userWithRole(UserRole::Sekretaris);

    $announcement = Announcement::factory()->create([
        'status' => ContentStatus::Draft,
        'created_by' => $sekretaris->id,
    ]);

    expect(AnnouncementResource::getNavigationBadge())->toBeNull();

    Livewire::actingAs($sekretaris)
        ->test(ListAnnouncements::class)
        ->callTableAction('submitForApproval', $announcement)
        ->assertHasNoTableActionErrors()
        ->assertDispatched('refresh-sidebar');

    expect(AnnouncementResource::getNavigationBadge())->toBe('1');
});
