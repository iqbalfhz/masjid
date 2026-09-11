<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Header keamanan untuk seluruh respons.
 *
 * Laravel tidak memasang satu pun dari header ini secara bawaan, dan pemindaian
 * securityheaders.com atas situs ini memberi nilai F. Semuanya bekerja di sisi
 * browser: ia menutup kelas serangan yang tidak bisa dicegah dari sisi server —
 * clickjacking lewat iframe, penebakan tipe berkas, kebocoran alamat halaman ke
 * situs luar, dan eksekusi skrip sisipan.
 *
 * Dipasang sebagai middleware, bukan di konfigurasi Caddy, karena dua hal:
 * salah sintaks di Caddyfile menjatuhkan seluruh container, sedangkan middleware
 * paling buruk hanya salah header; dan middleware ikut berpindah ke mana pun
 * aplikasi ini dipasang ulang (PRD bagian 13), tidak bergantung pada server
 * tertentu.
 *
 * Content-Security-Policy dibelah dua lewat parameter middleware: halaman publik
 * memakai kebijakan ketat, admin panel (didaftarkan dengan `:admin`) memakai
 * kebijakan yang dilonggarkan seperlunya untuk Alpine.js dan Livewire.
 */
class SecurityHeaders
{
    /**
     * CSP halaman publik — ketat.
     *
     * `script-src 'self'` tanpa 'unsafe-inline' adalah inti perlindungannya.
     * Artikel, pengumuman, dan FAQ memakai RichEditor dan dirender sebagai HTML
     * mentah, jadi <script> yang disisipkan lewat konten itu diblokir browser
     * alih-alih dieksekusi di hadapan jamaah. Syaratnya, view publik tidak boleh
     * memuat skrip inline — tests/Feature/ContentSecurityPolicyTest.php
     * menjaganya.
     *
     * @var array<string, list<string>>
     */
    private const PUBLIK = [
        'default-src' => ["'self'"],
        'script-src' => ["'self'"],
        // Empat atribut style bernilai dinamis (lebar grafik keuangan, jeda
        // animasi) tidak praktis dipindah. Injeksi style jauh kurang berbahaya
        // daripada skrip.
        'style-src' => ["'self'", "'unsafe-inline'"],
        // https: agar gambar yang ditempel pengurus di artikel dari situs lain
        // tetap tampil. Gambar tidak bisa mengeksekusi skrip.
        'img-src' => ["'self'", 'data:', 'blob:', 'https:'],
        'font-src' => ["'self'"],
        'connect-src' => ["'self'"],
        // Peta di halaman kontak (MosqueSetting::mapsEmbedSrc). maps.google.com
        // mengalihkan ke www.google.com, dan frame-src memeriksa setiap tujuan
        // pengalihan — keduanya harus diizinkan.
        'frame-src' => ['https://maps.google.com', 'https://www.google.com'],
        // Service worker pengingat sholat (public/sw.js).
        'worker-src' => ["'self'"],
        'object-src' => ["'none'"],
        'base-uri' => ["'self'"],
        'form-action' => ["'self'"],
        'frame-ancestors' => ["'self'"],
    ];

    /**
     * CSP admin panel — dilonggarkan seperlunya.
     *
     * Filament dibangun di atas Alpine.js (butuh 'unsafe-eval') dan Livewire
     * (menyisipkan skrip inline). Admin berada di balik login, jadi paparannya
     * jauh lebih kecil daripada halaman publik.
     *
     * @var array<string, list<string>>
     */
    private const ADMIN = [
        'default-src' => ["'self'"],
        'script-src' => ["'self'", "'unsafe-inline'", "'unsafe-eval'"],
        'style-src' => ["'self'", "'unsafe-inline'"],
        // Pengguna tanpa foto diberi avatar dari ui-avatars.com oleh Filament.
        'img-src' => ["'self'", 'data:', 'blob:', 'https://ui-avatars.com'],
        'font-src' => ["'self'", 'data:'],
        'connect-src' => ["'self'"],
        'frame-src' => ["'self'"],
        'object-src' => ["'none'"],
        'base-uri' => ["'self'"],
        'form-action' => ["'self'"],
        'frame-ancestors' => ["'self'"],
    ];

    /**
     * @param  string  $konteks  'publik' (bawaan) atau 'admin'
     */
    public function handle(Request $request, Closure $next, string $konteks = 'publik'): Response
    {
        $response = $next($request);

        /*
         * Paksa browser memakai HTTPS untuk kunjungan berikutnya, termasuk bila
         * pengguna mengetik alamat tanpa https.
         *
         * Hanya dikirim pada koneksi aman. Mengirimkannya lewat HTTP tidak ada
         * gunanya — penyerang yang bisa mengubah lalu lintas HTTP juga bisa
         * membuang header ini.
         */
        if ($request->secure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains',
            );
        }

        // Cegah situs lain memuat halaman ini di dalam iframe (clickjacking).
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Larang browser menebak tipe berkas dari isinya. Tanpa ini, berkas
        // unggahan jamaah bisa ditafsirkan sebagai skrip.
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Jangan bocorkan alamat halaman lengkap ke situs luar; cukup nama
        // domainnya saat berpindah ke situs lain.
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        /*
         * Matikan kemampuan browser yang tidak dipakai sistem ini.
         *
         * Sengaja tidak menyertakan notifikasi dan push: keduanya dipakai fitur
         * pengingat sholat (PRD 5.1.2).
         */
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=(), usb=()',
        );

        /*
         * Mode report-only mencatat pelanggaran di konsol browser tanpa
         * memblokir — lihat config/masjid.php bagian `csp`.
         */
        $response->headers->set(
            config('masjid.csp.report_only')
                ? 'Content-Security-Policy-Report-Only'
                : 'Content-Security-Policy',
            $this->kebijakan($konteks === 'admin' ? self::ADMIN : self::PUBLIK),
        );

        return $response;
    }

    /**
     * @param  array<string, list<string>>  $arahan
     */
    private function kebijakan(array $arahan): string
    {
        return collect($arahan)
            ->map(fn (array $sumber, string $nama): string => $nama.' '.implode(' ', $sumber))
            ->implode('; ');
    }
}
