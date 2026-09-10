<?php

use App\Enums\UserRole;
use App\Filament\Pages\ManageMosqueSetting;
use App\Filament\Resources\Announcements\AnnouncementResource;
use App\Filament\Resources\Announcements\Pages\CreateAnnouncement;
use App\Filament\Resources\Announcements\Pages\EditAnnouncement;
use App\Filament\Resources\FinanceCategories\FinanceCategoryResource;
use App\Filament\Resources\FinanceCategories\Pages\CreateFinanceCategory;
use App\Filament\Widgets\ActivityCalendarWidget;
use App\Models\Announcement;
use App\Models\FinanceCategory;
use App\Models\Study;
use Livewire\Livewire;

beforeEach(function (): void {
    seedMasterData();
});

it('kembali ke daftar setelah membuat record baru', function (): void {
    $this->actingAs(userWithRole(UserRole::Sekretaris));

    Livewire::test(CreateAnnouncement::class)
        ->fillForm([
            'title' => 'Kerja Bakti Akhir Pekan',
            'content' => '<p>Mengajak jamaah kerja bakti membersihkan masjid.</p>',
            'start_date' => today()->toDateString(),
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect(AnnouncementResource::getUrl('index'));

    expect(Announcement::query()->where('title', 'Kerja Bakti Akhir Pekan')->exists())->toBeTrue();
});

it('kembali ke daftar setelah menyimpan perubahan', function (): void {
    $this->actingAs(userWithRole(UserRole::Sekretaris));

    $announcement = Announcement::factory()->create();

    // Model memakai slug sebagai route key, termasuk di dalam panel admin.
    Livewire::test(EditAnnouncement::class, ['record' => $announcement->getRouteKey()])
        ->fillForm(['title' => 'Judul Yang Diperbarui'])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertRedirect(AnnouncementResource::getUrl('index'));

    expect($announcement->fresh()->title)->toBe('Judul Yang Diperbarui');
});

it('kembali ke daftar pada modul lain juga', function (): void {
    $this->actingAs(userWithRole(UserRole::Bendahara));

    Livewire::test(CreateFinanceCategory::class)
        ->fillForm([
            'name' => 'Infaq Kotak Amal Lantai 3',
            'type' => 'in',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect(FinanceCategoryResource::getUrl('index'));

    expect(FinanceCategory::query()->where('name', 'Infaq Kotak Amal Lantai 3')->exists())->toBeTrue();
});

it('hanya menyediakan satu tombol simpan di Pengaturan Umum', function (): void {
    $this->actingAs(userWithRole(UserRole::Superadmin));

    $page = new ManageMosqueSetting;

    expect($page->getFormActions())->toHaveCount(1)
        ->and($page->getFormActions()[0]->getLabel())->toBe('Simpan pengaturan');
});

it('menyembunyikan tombol simpan dari role yang tidak boleh mengubah pengaturan', function (): void {
    $this->actingAs(userWithRole(UserRole::KetuaDkm));

    expect((new ManageMosqueSetting)->canEdit())->toBeFalse();
});

it('tidak menawarkan pembuatan agenda langsung dari kalender', function (): void {
    $this->actingAs(userWithRole(UserRole::Superadmin));

    $widget = new ActivityCalendarWidget;
    $refleksi = new ReflectionMethod($widget, 'headerActions');

    expect($refleksi->invoke($widget))->toBeEmpty();
});

it('menautkan tiap agenda kalender ke record aslinya', function (): void {
    $this->actingAs(userWithRole(UserRole::Superadmin));

    $announcement = Study::factory()->approved()->incidental()->create([
        'start_date' => today(),
        'time' => '19:30:00',
    ]);

    $events = (new ActivityCalendarWidget)->fetchEvents([
        'start' => today()->subDay()->toDateString(),
        'end' => today()->addDay()->toDateString(),
    ]);

    expect($events)->not->toBeEmpty()
        ->and($events[0]['url'])->toBe(route('filament.admin.resources.studies.edit', $announcement));
});
