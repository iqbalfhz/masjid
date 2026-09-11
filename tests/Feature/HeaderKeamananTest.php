<?php

use App\Enums\UserRole;

beforeEach(function (): void {
    seedMasterData();
});

it('memasang header keamanan di halaman publik', function (): void {
    // Pemindaian securityheaders.com atas situs ini memberi nilai F: tidak satu
    // pun header ini terpasang. Semuanya bekerja di sisi browser dan menutup
    // kelas serangan yang tidak bisa dicegah dari sisi server.
    $response = $this->get('/');

    $response->assertSuccessful()
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

    expect($response->headers->get('Permissions-Policy'))
        ->toContain('camera=()')
        ->toContain('microphone=()');
});

it('memasang header keamanan di admin panel juga', function (): void {
    $this->actingAs(userWithRole(UserRole::KetuaDkm))
        ->get('/admin')
        ->assertSuccessful()
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('X-Content-Type-Options', 'nosniff');
});

it('mengirim HSTS hanya pada koneksi aman', function (): void {
    // Mengirimkan HSTS lewat HTTP tidak ada gunanya: penyerang yang mampu
    // mengubah lalu lintas HTTP juga mampu membuang header ini. Yang lebih
    // penting, di pengembangan lokal tanpa HTTPS ia akan mengunci browser
    // pengembang ke https untuk domain itu — sulit dibatalkan.
    $this->get('/')->assertHeaderMissing('Strict-Transport-Security');

    $this->get('https://localhost/')
        ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
});

it('tidak mematikan izin yang dibutuhkan pengingat sholat', function (): void {
    // Web Push (PRD 5.1.2) bergantung pada izin notifikasi. Mencantumkannya di
    // Permissions-Policy akan mematikan fitur itu diam-diam.
    $izin = $this->get('/')->headers->get('Permissions-Policy');

    expect($izin)->not->toContain('notifications')
        ->and($izin)->not->toContain('push');
});
