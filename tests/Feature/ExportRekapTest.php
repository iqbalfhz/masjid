<?php

use App\Enums\UserRole;
use App\Filament\Exports\FinanceTransactionExporter;
use App\Filament\Exports\QurbanRegistrationExporter;
use App\Filament\Exports\ZakatRegistrationExporter;
use App\Filament\Resources\FinanceTransactions\Pages\ListFinanceTransactions;
use App\Filament\Resources\QurbanRegistrations\Pages\ListQurbanRegistrations;
use App\Filament\Resources\ZakatRegistrations\Pages\ListZakatRegistrations;
use App\Models\FinanceTransaction;
use App\Models\QurbanRegistration;
use App\Models\ZakatRegistration;
use Filament\Actions\Exports\ContentGenerators\CsvExportContentGenerator;
use Filament\Actions\Exports\Models\Export;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;

beforeEach(function (): void {
    seedMasterData();
});

/**
 * Jalankan aksi export pada sebuah halaman daftar, lalu kembalikan isi CSV
 * utuh yang diterima pengurus.
 *
 * Filament menulis data ke berkas potongan dan baru merangkainya bersama baris
 * judul saat diunduh, jadi pengujian memakai generator yang sama dengan proses
 * unduhan — bukan membaca potongannya satu per satu.
 */
function jalankanExport(string $halaman): string
{
    Livewire::test($halaman)
        ->callTableAction('export')
        ->assertHasNoActionErrors();

    $export = Export::query()->latest('id')->firstOrFail();

    expect($export->completed_at)->not->toBeNull('Export tidak pernah selesai — cek koneksi antrean.');

    $isi = '';

    foreach (app(CsvExportContentGenerator::class)($export) as $potongan) {
        $isi .= $potongan;
    }

    return $isi;
}

it('mengekspor rekap keuangan dengan judul kolom berbahasa Indonesia', function (): void {
    $this->actingAs(userWithRole(UserRole::Bendahara));

    FinanceTransaction::factory()->income()->create([
        'amount' => 750000,
        'description' => 'Infaq Jumat pekan pertama',
        'date' => today(),
    ]);

    $isi = jalankanExport(ListFinanceTransactions::class);

    expect($isi)
        ->toContain('Tanggal')
        ->toContain('Pemasukan')
        ->toContain('Pengeluaran')
        ->toContain('Infaq Jumat pekan pertama')
        // Nominal masuk ke lajur pemasukan dengan format ribuan Indonesia.
        ->toContain('750.000');
});

it('memisahkan pemasukan dan pengeluaran ke lajur berbeda', function (): void {
    $this->actingAs(userWithRole(UserRole::Bendahara));

    FinanceTransaction::factory()->expense()->create([
        'amount' => 200000,
        'description' => 'Tagihan listrik',
        'date' => today(),
    ]);

    $baris = collect(explode("\n", jalankanExport(ListFinanceTransactions::class)))
        ->first(fn (string $b): bool => str_contains($b, 'Tagihan listrik'));

    // Kolom: Tanggal, Kategori, Jenis, Keterangan, Nomor bukti, Pemasukan, Pengeluaran, Diinput oleh
    $kolom = str_getcsv($baris);

    expect($kolom[2])->toBe('Pengeluaran')
        ->and($kolom[5])->toBe('')
        ->and($kolom[6])->toBe('200.000');
});

it('mengekspor rekap kurban lengkap dengan status pembayaran', function (): void {
    $this->actingAs(userWithRole(UserRole::Bendahara));

    $pendaftaran = QurbanRegistration::factory()->create([
        'name' => 'Pak Hasan',
        'animal_type' => 'kambing',
        'quantity' => 2,
    ]);

    $isi = jalankanExport(ListQurbanRegistrations::class);

    expect($isi)
        ->toContain('Nomor pendaftaran')
        ->toContain('Jenis hewan')
        ->toContain($pendaftaran->registration_number)
        ->toContain('Pak Hasan')
        // Enum diterjemahkan, bukan nilai mentah "kambing"/"belum_bayar".
        ->toContain('Kambing')
        ->toContain('Belum Bayar');
});

it('mengekspor rekap zakat beserta jumlah jiwa', function (): void {
    $this->actingAs(userWithRole(UserRole::Bendahara));

    ZakatRegistration::factory()->create([
        'name' => 'Bu Aminah',
        'soul_count' => 4,
    ]);

    $isi = jalankanExport(ListZakatRegistrations::class);

    expect($isi)
        ->toContain('Nama muzakki')
        ->toContain('Jumlah jiwa')
        ->toContain('Bu Aminah')
        ->toContain('Zakat Fitrah');
});

it('menjalankan export tanpa bergantung pada queue worker', function (): void {
    // Bila export dilempar ke antrean sementara worker tidak jalan, berkasnya
    // tidak pernah jadi dan pengurus hanya melihat notifikasi menggantung.
    foreach ([FinanceTransactionExporter::class, QurbanRegistrationExporter::class, ZakatRegistrationExporter::class] as $exporter) {
        $instance = new $exporter(new Export, [], []);

        expect($instance->getJobConnection())->toBe('sync');
    }
});

it('menghormati filter yang sedang aktif saat mengekspor', function (): void {
    $this->actingAs(userWithRole(UserRole::Bendahara));

    FinanceTransaction::factory()->income()->create(['description' => 'Masuk hitungan', 'date' => today()]);
    FinanceTransaction::factory()->income()->create(['description' => 'Di luar rentang', 'date' => today()->subYear()]);

    Livewire::test(ListFinanceTransactions::class)
        ->filterTable('date_range', ['from' => today()->subDay()->toDateString()])
        ->callTableAction('export')
        ->assertHasNoActionErrors();

    $export = Export::query()->latest('id')->firstOrFail();

    expect($export->successful_rows)->toBe(1);
});

it('membuat notifikasi export hilang sendiri, tidak menetap di layar', function (): void {
    $bendahara = userWithRole(UserRole::Bendahara);
    $this->actingAs($bendahara);

    FinanceTransaction::factory()->income()->create(['date' => today()]);

    Livewire::test(ListFinanceTransactions::class)
        ->callTableAction('export')
        ->assertHasNoActionErrors();

    // Filament menetapkan toast selamanya pada mode sinkron; di sini durasinya
    // dibatasi agar kotak notifikasi tidak menumpuk dan harus ditutup manual.
    $notifikasi = Notification::make()->title('contoh')->body('isi');
    $hasil = FinanceTransactionExporter::modifyCompletedNotification($notifikasi, Export::query()->latest('id')->firstOrFail());

    expect($hasil->getDuration())->not->toBe('persistent')
        ->and((int) $hasil->getDuration())->toBeGreaterThan(0);
});

it('menyimpan tautan unduhan di lonceng agar tidak ikut hilang', function (): void {
    $bendahara = userWithRole(UserRole::Bendahara);
    $this->actingAs($bendahara);

    QurbanRegistration::factory()->count(2)->create();

    Livewire::test(ListQurbanRegistrations::class)
        ->callTableAction('export')
        ->assertHasNoActionErrors();

    $tersimpan = $bendahara->fresh()->notifications()->latest()->first();

    expect($tersimpan)->not->toBeNull('Tautan unduhan hilang bersama toast-nya.')
        ->and($tersimpan->data['title'])->toContain('kurban')
        ->and($tersimpan->data['actions'])->not->toBeEmpty();
});

it('tidak menetapkan notifikasi selain pesan kegagalan', function (): void {
    // Notifikasi sukses harus hilang sendiri; hanya pesan gagal yang boleh
    // menetap agar pengurus tidak melewatkannya.
    $menetap = collect(File::allFiles(app_path()))
        ->filter(fn ($f): bool => str_ends_with($f->getFilename(), '.php'))
        ->filter(fn ($f): bool => str_contains(File::get($f->getPathname()), '->persistent()'))
        ->map(fn ($f): string => $f->getFilename())
        ->values()
        ->all();

    expect($menetap)->toBe(['ListPrayerSchedules.php']);
});
