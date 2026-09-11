<?php

use Illuminate\Support\Facades\Route;

/**
 * Aplikasi ini dijalankan di belakang proxy (Coolify menyelesaikan TLS di
 * Traefik, aplikasinya sendiri berbicara HTTP di jaringan internal Docker).
 *
 * Bila proxy tidak dipercaya, Laravel menganggap setiap request datang sebagai
 * HTTP dan membangun URL berawalan http:// — browser lalu memblokirnya sebagai
 * konten campuran, dan Web Push berhenti bekerja karena Service Worker menuntut
 * konteks aman. Semuanya tanpa satu pun pesan error.
 */
beforeEach(function (): void {
    Route::get('/__uji-proxy', fn (): array => [
        'secure' => request()->secure(),
        'skema' => request()->getScheme(),
        'url' => url('/contoh'),
    ]);
});

it('mengenali request sebagai aman saat proxy meneruskan X-Forwarded-Proto', function (): void {
    $this->get('/__uji-proxy', ['X-Forwarded-Proto' => 'https'])
        ->assertSuccessful()
        ->assertJson([
            'secure' => true,
            'skema' => 'https',
        ]);
});

it('membangun URL https di belakang proxy, bukan http', function (): void {
    // Inilah yang membuat halaman publik memuat aset lewat http lalu diblokir
    // browser, dan yang membuat pendaftaran Web Push ditolak.
    $data = $this->get('/__uji-proxy', ['X-Forwarded-Proto' => 'https'])->json();

    expect($data['url'])->toStartWith('https://');
});

it('meneruskan alamat asli pengunjung, bukan alamat proxy', function (): void {
    // Rate limiting form publik (PRD bagian 6) memakai alamat IP. Bila semua
    // request terlihat berasal dari proxy, satu pengirim spam bisa mengunci
    // seluruh jamaah sekaligus.
    Route::get('/__uji-ip', fn (): array => ['ip' => request()->ip()]);

    $this->get('/__uji-ip', [
        'X-Forwarded-For' => '203.0.113.9',
        'X-Forwarded-Proto' => 'https',
    ])->assertJson(['ip' => '203.0.113.9']);
});

it('tetap menganggap http saat tidak ada header proxy', function (): void {
    // Tanpa header dari proxy, tidak boleh ada yang mengarang konteks aman.
    $this->get('/__uji-proxy')->assertJson(['secure' => false]);
});
