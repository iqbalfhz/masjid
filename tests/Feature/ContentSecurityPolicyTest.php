<?php

use App\Enums\UserRole;
use Illuminate\Support\Facades\File;

beforeEach(function (): void {
    seedMasterData();
});

/**
 * Ambil daftar sumber untuk satu arahan CSP.
 *
 * @return list<string>
 */
function sumberCsp(?string $csp, string $arahan): array
{
    foreach (explode(';', (string) $csp) as $bagian) {
        $token = preg_split('/\s+/', trim($bagian));

        if (($token[0] ?? null) === $arahan) {
            return array_values(array_slice($token, 1));
        }
    }

    return [];
}

it('menegakkan CSP ketat di halaman publik: skrip hanya dari origin sendiri', function (): void {
    // Inti perlindungannya. Artikel, pengumuman, dan FAQ dirender sebagai HTML
    // mentah dari RichEditor; tanpa 'unsafe-inline', <script> yang disisipkan
    // lewat konten itu diblokir browser alih-alih dieksekusi di hadapan jamaah.
    $csp = $this->get('/')->assertSuccessful()->headers->get('Content-Security-Policy');

    expect(sumberCsp($csp, 'script-src'))->toBe(["'self'"])
        ->and(sumberCsp($csp, 'object-src'))->toBe(["'none'"])
        ->and(sumberCsp($csp, 'frame-ancestors'))->toBe(["'self'"])
        ->and(sumberCsp($csp, 'base-uri'))->toBe(["'self'"]);
});

it('mengizinkan peta Google di halaman kontak', function (): void {
    // maps.google.com mengalihkan ke www.google.com, dan frame-src memeriksa
    // setiap tujuan pengalihan — keduanya harus diizinkan.
    $csp = $this->get('/kontak')->assertSuccessful()->headers->get('Content-Security-Policy');

    expect(sumberCsp($csp, 'frame-src'))
        ->toContain('https://maps.google.com')
        ->toContain('https://www.google.com');
});

it('memberi admin panel CSP yang cukup longgar untuk Alpine dan Livewire', function (): void {
    $csp = $this->actingAs(userWithRole(UserRole::KetuaDkm))
        ->get('/admin')
        ->assertSuccessful()
        ->headers->get('Content-Security-Policy');

    expect(sumberCsp($csp, 'script-src'))
        ->toContain("'unsafe-eval'")
        ->toContain("'unsafe-inline'")
        ->and(sumberCsp($csp, 'img-src'))->toContain('https://ui-avatars.com');
});

it('memakai CSP admin juga di halaman login, bukan kebijakan publik', function (): void {
    // Halaman login Filament sendiri berjalan di atas Alpine dan Livewire. Bila
    // ia mendapat kebijakan publik yang ketat, formulirnya mati dan tidak ada
    // seorang pun yang bisa masuk.
    $csp = $this->get('/admin/login')->assertSuccessful()->headers->get('Content-Security-Policy');

    expect(sumberCsp($csp, 'script-src'))->toContain("'unsafe-eval'");
});

it('bisa diturunkan menjadi report-only lewat konfigurasi', function (): void {
    // Sakelar darurat: bila CSP mematahkan sesuatu di produksi, isi
    // CSP_REPORT_ONLY=true di Coolify lalu Restart — tanpa mengubah kode.
    config(['masjid.csp.report_only' => true]);

    $response = $this->get('/');

    expect($response->headers->get('Content-Security-Policy-Report-Only'))->not->toBeNull()
        ->and($response->headers->has('Content-Security-Policy'))->toBeFalse();
});

/**
 * View publik yang cocok dengan pola — dipakai untuk mencari kode yang akan
 * diblokir CSP publik.
 *
 * @return list<string>
 */
function viewPublikYangMemuat(string $pola): array
{
    return collect(File::allFiles(resource_path('views/public')))
        ->merge(File::allFiles(resource_path('views/layouts')))
        ->merge(File::allFiles(resource_path('views/components')))
        ->filter(fn ($berkas): bool => preg_match($pola, $berkas->getContents()) === 1)
        ->map(fn ($berkas): string => $berkas->getRelativePathname())
        ->values()
        ->all();
}

it('tidak menyisakan skrip inline di view publik', function (): void {
    // CSP publik tanpa 'unsafe-inline' hanya aman bila memang tidak ada skrip
    // inline. Skrip inline baru di view publik akan diam-diam diblokir browser —
    // test ini menangkapnya lebih dulu, sebelum jamaah yang menemukannya.
    expect(viewPublikYangMemuat('/<script(?![^>]*\bsrc=)[^>]*>/i'))->toBe([]);
});

it('tidak menyisakan event handler inline di view publik', function (): void {
    // onclick, onerror, onchange, dan sejenisnya diblokir sama seperti <script>
    // inline, dan kegagalannya tidak terlihat: filter kategori kegiatan tidak
    // bereaksi, gambar rusak tidak diganti placeholder. Keduanya sempat memakai
    // atribut ini dan tertangkap saat CSP dipasang; perilakunya kini di app.js.
    expect(viewPublikYangMemuat('/\son[a-z]+\s*=\s*["\']/i'))->toBe([]);
});
