<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        /*
         * Percayai proxy di depan aplikasi.
         *
         * Di Coolify (dan susunan berbasis container lainnya), aplikasi berjalan
         * di jaringan internal sementara TLS diselesaikan oleh proxy di depannya.
         * Tanpa ini Laravel menganggap tiap request datang sebagai HTTP, lalu:
         *
         * - membangun URL berawalan http:// di halaman yang diakses lewat https,
         *   sehingga browser memblokirnya sebagai konten campuran;
         * - membuat Web Push gagal, karena Service Worker menuntut konteks aman;
         * - berpotensi memicu putaran pengalihan bila HTTPS dipaksakan di proxy.
         *
         * Kegagalannya tidak bersuara — di server tampak wajar sampai ada yang
         * memeriksa alamat yang dihasilkan.
         *
         * Nilai bawaannya mempercayai seluruh proxy, karena container hanya bisa
         * dijangkau lewat proxy itu. Bila suatu pemasangan mengekspos aplikasi
         * langsung ke internet, isi TRUSTED_PROXIES dengan alamat proxy-nya saja
         * agar header X-Forwarded-* tidak bisa dipalsukan pengunjung.
         */
        $middleware->trustProxies(at: env('TRUSTED_PROXIES', '*'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
