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
 * clickjacking lewat iframe, penebakan tipe berkas, dan kebocoran alamat halaman
 * ke situs luar.
 *
 * Dipasang sebagai middleware, bukan di konfigurasi Caddy, karena dua hal:
 * salah sintaks di Caddyfile menjatuhkan seluruh container, sedangkan middleware
 * paling buruk hanya salah header; dan middleware ikut berpindah ke mana pun
 * aplikasi ini dipasang ulang (PRD bagian 13), tidak bergantung pada server
 * tertentu.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
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

        return $response;
    }
}
