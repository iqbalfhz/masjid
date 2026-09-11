<?php

use App\Enums\ContentStatus;
use App\Enums\SuggestionStatus;
use App\Enums\TransactionType;
use App\Enums\UserRole;
use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\ApprovalQueueWidget;
use App\Filament\Widgets\FinanceTrendWidget;
use App\Filament\Widgets\JamaahInputWidget;
use App\Filament\Widgets\MosqueOverviewWidget;
use App\Filament\Widgets\SystemHealthWidget;
use App\Models\Announcement;
use App\Models\Article;
use App\Models\FinanceTransaction;
use App\Models\PrayerSchedule;
use App\Models\Study;
use App\Models\Suggestion;
use Livewire\Livewire;

beforeEach(function (): void {
    seedMasterData();
});

it('menampilkan antrean approval lintas modul dalam satu tabel', function (): void {
    // Nilai utamanya justru di pencampuran modul: Ketua DKM melihat seluruh
    // pekerjaannya di satu tempat, bukan tersebar di empat menu sidebar.
    $ketua = userWithRole(UserRole::KetuaDkm);

    Announcement::factory()->create([
        'title' => 'Kerja Bakti Minggu Pagi',
        'status' => ContentStatus::MenungguApproval,
    ]);
    Study::factory()->create([
        'theme' => 'Tafsir Surat Al-Kahfi',
        'status' => ContentStatus::MenungguApproval,
    ]);
    Article::factory()->create([
        'title' => 'Adab Menuntut Ilmu',
        'status' => ContentStatus::MenungguApproval,
    ]);

    Livewire::actingAs($ketua)
        ->test(ApprovalQueueWidget::class)
        ->assertSuccessful()
        ->assertSee('Kerja Bakti Minggu Pagi')
        ->assertSee('Tafsir Surat Al-Kahfi')
        ->assertSee('Adab Menuntut Ilmu')
        ->assertSee('Pengumuman')
        ->assertSee('Kajian')
        ->assertSee('Artikel');
});

it('menyetujui konten langsung dari antrean dashboard', function (): void {
    // Inti perbaikannya: tugas tersering selesai tanpa berpindah halaman.
    $ketua = userWithRole(UserRole::KetuaDkm);

    $study = Study::factory()->create([
        'theme' => 'Fiqih Muamalah',
        'status' => ContentStatus::MenungguApproval,
    ]);

    Livewire::actingAs($ketua)
        ->test(ApprovalQueueWidget::class)
        ->callTableAction('approve', 'kajian:'.$study->id, ['approval_note' => 'Sudah sesuai.'])
        ->assertHasNoTableActionErrors();

    expect($study->fresh()->status)->toBe(ContentStatus::Disetujui)
        ->and($study->fresh()->reviewed_by)->toBe($ketua->id);
});

it('menyusun dashboard menurut wewenang tiap peran', function (): void {
    // Judul "Perlu Tindakan" menjanjikan tindakan milik pembacanya. Menampilkan
    // antrean approval kepada Bendahara — yang menurut matriks PRD 5.3 hanya
    // punya hak baca pada Pengumuman — membuat janji itu tidak ditepati.
    $papan = function (UserRole $peran): array {
        $this->actingAs(userWithRole($peran));

        return collect((new Dashboard)->getWidgets())
            ->filter(fn (string $widget): bool => $widget::canView())
            ->map(fn (string $widget): string => class_basename($widget))
            ->values()
            ->all();
    };

    expect($papan(UserRole::KetuaDkm))
        ->toContain('ApprovalQueueWidget')
        ->not->toContain('JamaahInputWidget');

    expect($papan(UserRole::Sekretaris))
        ->toContain('JamaahInputWidget')
        ->not->toContain('ApprovalQueueWidget');

    expect($papan(UserRole::Bendahara))
        ->toContain('FinanceTrendWidget')
        ->not->toContain('ApprovalQueueWidget')
        ->not->toContain('JamaahInputWidget');

    // Admin mengawasi seluruh operasional, jadi ia memang melihat papan penuh.
    expect($papan(UserRole::Admin))
        ->toContain('ApprovalQueueWidget')
        ->toContain('JamaahInputWidget')
        ->toContain('SystemHealthWidget');
});

it('memperingatkan saat jadwal sholat hampir habis', function (): void {
    // Kegagalan senyap: bila cron mati, jadwal di website publik jadi basi dan
    // yang pertama tahu adalah jamaah yang salah datang waktu subuh.
    PrayerSchedule::query()->delete();

    $this->actingAs(userWithRole(UserRole::Admin));

    Livewire::test(SystemHealthWidget::class)
        ->assertSuccessful()
        ->assertSee('Habis')
        ->assertSee('Belum pernah tersinkron');
});

it('menampilkan sisa hari jadwal sholat yang masih tersedia', function (): void {
    PrayerSchedule::query()->delete();
    PrayerSchedule::factory()->create(['date' => today()->addDays(10)]);

    $this->actingAs(userWithRole(UserRole::Admin));

    Livewire::test(SystemHealthWidget::class)
        ->assertSuccessful()
        ->assertSee('10 hari lagi');
});

it('menyebut zona waktu sesuai konfigurasi, bukan WIB tetap', function (): void {
    // Kodebase ini dipasang ulang per masjid (PRD bagian 13); masjid di Makassar
    // akan melihat jam WITA-nya diberi label WIB.
    config(['app.timezone' => 'Asia/Makassar']);
    date_default_timezone_set('Asia/Makassar');

    $this->actingAs(userWithRole(UserRole::Admin));

    Livewire::test(MosqueOverviewWidget::class)
        ->assertSuccessful()
        ->assertDontSee('WIB');
})->after(function (): void {
    date_default_timezone_set('Asia/Jakarta');
});

it('menampilkan masukan jamaah beserta isinya, bukan sekadar jumlah', function (): void {
    $sekretaris = userWithRole(UserRole::Sekretaris);

    Suggestion::factory()->create([
        'name' => 'Pak Hadi',
        'message' => 'Atap serambi bocor saat hujan deras.',
        'status' => SuggestionStatus::Baru,
    ]);

    Livewire::actingAs($sekretaris)
        ->test(JamaahInputWidget::class)
        ->assertSuccessful()
        ->assertSee('Pak Hadi')
        ->assertSee('Atap serambi bocor');
});

it('menggambar tren keuangan enam bulan terakhir', function (): void {
    $bendahara = userWithRole(UserRole::Bendahara);

    FinanceTransaction::factory()->create([
        'type' => TransactionType::In,
        'amount' => 5_000_000,
        'date' => today(),
    ]);

    Livewire::actingAs($bendahara)
        ->test(FinanceTrendWidget::class)
        ->assertSuccessful()
        ->assertSee('Tren Keuangan');

    // Enam label bulan, dua dataset — dipastikan langsung ke datanya karena
    // grafiknya sendiri digambar di sisi browser.
    $data = (fn () => $this->getData())->call(new FinanceTrendWidget);

    expect($data['labels'])->toHaveCount(6)
        ->and($data['datasets'])->toHaveCount(2)
        ->and($data['datasets'][0]['data'])->toHaveCount(6)
        ->and(end($data['datasets'][0]['data']))->toBeGreaterThanOrEqual(5_000_000.0);
});
