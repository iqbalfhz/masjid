<?php

use Illuminate\Support\Facades\File;

/**
 * @return array<string, string> nama berkas => isi
 */
function viewPublik(): array
{
    return collect(File::allFiles(resource_path('views/public')))
        ->merge(File::allFiles(resource_path('views/layouts')))
        ->merge(File::allFiles(resource_path('views/components/public')))
        ->filter(fn ($f): bool => str_ends_with($f->getFilename(), '.blade.php'))
        ->mapWithKeys(fn ($f): array => [
            str_replace(resource_path('views').DIRECTORY_SEPARATOR, '', $f->getPathname()) => $f->getContents(),
        ])
        ->all();
}

it('menjaga grid dua kolom tetap bisa menyusut di layar ponsel', function (): void {
    // Grid yang hanya mendefinisikan kolom di breakpoint `lg:` akan jatuh ke
    // satu kolom `auto` pada ponsel, dan kolom `auto` tidak boleh menyusut di
    // bawah min-content isinya. Begitu di dalamnya ada tabel `min-w-160`,
    // seluruh halaman terkunci selebar 640px dan bisa digeser ke samping —
    // persis gejala yang terlihat di HP.
    //
    // `grid-cols-1` Tailwind menghasilkan repeat(1, minmax(0, 1fr)), dan
    // minmax(0, …) itulah yang mengizinkan kolomnya menyusut.
    $pelanggar = [];

    foreach (viewPublik() as $berkas => $isi) {
        preg_match_all('/class="([^"]*lg:grid-cols-\[[^"]*)"/', $isi, $cocok);

        foreach ($cocok[1] as $kelas) {
            if (! str_contains($kelas, 'grid-cols-1')) {
                $pelanggar[] = "{$berkas}: {$kelas}";
            }
        }
    }

    expect($pelanggar)->toBe([], "Grid berikut belum punya grid-cols-1 untuk layar ponsel:\n".implode("\n", $pelanggar));
});

it('membungkus setiap tabel lebar dengan area gulir horizontal', function (): void {
    // Tabel memang boleh lebih lebar dari layar — asal ia sendiri yang
    // digulir, bukan seluruh halaman ikut bergeser.
    foreach (viewPublik() as $berkas => $isi) {
        preg_match_all('/<table[^>]*class="[^"]*min-w-[^"]*"/', $isi, $cocok, PREG_OFFSET_CAPTURE);

        foreach ($cocok[0] as [$tag, $posisi]) {
            $sebelum = substr($isi, max(0, $posisi - 300), min($posisi, 300));

            // Sengaja assertStringContainsString, bukan expect()->toContain():
            // toContain() bersifat variadic, sehingga argumen kedua diperlakukan
            // sebagai needle tambahan dan pesan penjelas malah ikut dicari.
            $this->assertStringContainsString(
                'overflow-x-auto',
                $sebelum,
                "Tabel lebar di {$berkas} tidak dibungkus area gulir horizontal",
            );
        }
    }
});

it('memasang meta viewport agar ponsel memakai lebar layarnya sendiri', function (): void {
    // Tanpa ini ponsel merender halaman di viewport semu ~980px lalu
    // mengecilkannya, sehingga semua teks tampak kecil.
    $this->get('/')
        ->assertSuccessful()
        ->assertSee('name="viewport"', escape: false)
        ->assertSee('width=device-width', escape: false);
});
