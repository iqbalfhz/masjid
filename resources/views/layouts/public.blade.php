<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1d3b34">

    <title>@yield('title', $setting->name) — {{ $setting->name }}</title>
    <meta name="description" content="@yield('description', $setting->description ?? $setting->tagline)">

    <link rel="icon" href="{{ $setting->logo ? Storage::url($setting->logo) : asset('images/icon-192.png') }}">

    {{--
        Manifest membuat situs bisa dipasang ke Layar Utama. Di iPhone itu bukan
        sekadar pintasan: Safari hanya mengizinkan Web Push untuk web app yang
        terpasang, jadi tanpa baris ini pengingat sholat mustahil diaktifkan
        dari iPhone.
    --}}
    <link rel="manifest" href="{{ route('site.webmanifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">
    <meta name="apple-mobile-web-app-title" content="{{ $setting->name }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
{{-- overflow-x-clip (bukan hidden) agar header sticky tetap berfungsi. --}}
<body class="min-h-screen overflow-x-clip bg-masjid-50 font-sans text-masjid-950 antialiased">

<a href="#konten" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:text-masjid-700 focus:shadow-lg">
    Lompat ke konten utama
</a>

<header data-site-header
        class="sticky top-0 z-40 border-b border-masjid-100/80 bg-white/80 backdrop-blur-xl transition-shadow duration-300 data-[scrolled]:shadow-[0_10px_30px_-24px_rgba(29,59,52,0.7)]">
    <div class="mx-auto flex max-w-6xl items-center gap-2 px-4 py-3 sm:gap-4">
        <a href="{{ route('home') }}" class="group flex min-w-0 items-center gap-3">
            @if ($setting->logo)
                <img src="{{ Storage::url($setting->logo) }}" alt=""
                     class="h-10 w-10 shrink-0 rounded-xl object-cover ring-1 ring-masjid-100">
            @else
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-linear-to-br from-masjid-600 to-masjid-800 text-lg font-semibold text-white shadow-sm transition-transform duration-300 group-hover:scale-105"
                      aria-hidden="true">ﷺ</span>
            @endif
            <span class="min-w-0 leading-tight">
                <span class="block truncate text-base font-semibold text-masjid-800">{{ $setting->name }}</span>
                {{-- Alamat disembunyikan di layar sempit agar header tidak meluber. --}}
                <span class="hidden truncate text-xs text-masjid-500 sm:block">{{ $setting->address }}</span>
            </span>
        </a>

        <nav class="ms-auto hidden items-center gap-0.5 lg:flex" aria-label="Navigasi utama">
            @foreach ($navigation as $item)
                <a href="{{ $item['url'] }}"
                   @class([
                       'relative rounded-lg px-3 py-2 text-sm font-medium transition-colors duration-200',
                       'text-masjid-900' => $item['active'],
                       'text-masjid-600 hover:text-masjid-900' => ! $item['active'],
                   ])
                   @if ($item['active']) aria-current="page" @endif>
                    {{ $item['label'] }}
                    @if ($item['active'])
                        <span class="absolute inset-x-3 -bottom-0.5 h-0.5 rounded-full bg-linear-to-r from-emas-400 to-masjid-500"></span>
                    @endif
                </a>
            @endforeach
        </nav>

        <a href="{{ route('donasi') }}"
           class="ms-auto shrink-0 rounded-xl bg-linear-to-br from-emas-400 to-emas-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:shadow-[0_10px_24px_-12px_rgba(190,103,30,0.9)] hover:brightness-105 sm:px-4 lg:ms-2">
            Donasi
        </a>

        <button type="button"
                class="shrink-0 rounded-xl p-2 text-masjid-700 transition-colors hover:bg-masjid-50 lg:hidden"
                aria-controls="menu-utama"
                aria-expanded="false"
                data-menu-toggle>
            <span class="sr-only">Buka menu navigasi</span>
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/>
            </svg>
        </button>
    </div>

    <nav id="menu-utama" hidden class="border-t border-masjid-100 bg-white px-4 pb-4 lg:hidden" aria-label="Navigasi utama (mobile)">
        <ul class="grid gap-1 pt-2">
            @foreach ($navigation as $item)
                <li>
                    <a href="{{ $item['url'] }}"
                       @class([
                           'block rounded-lg px-3 py-2.5 text-sm font-medium transition-colors',
                           'bg-masjid-50 text-masjid-900' => $item['active'],
                           'text-masjid-700 hover:bg-masjid-50' => ! $item['active'],
                       ])>
                        {{ $item['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
</header>

@if ($runningAnnouncements->isNotEmpty())
    <div class="border-b border-masjid-800/20 bg-masjid-900 text-white">
        <div class="mx-auto flex max-w-6xl items-center gap-4 px-4 py-2.5 text-sm">
            <span class="inline-flex shrink-0 items-center gap-2 rounded-full bg-emas-500/95 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide">
                <span class="relative flex h-1.5 w-1.5" aria-hidden="true">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-white opacity-75"></span>
                    <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-white"></span>
                </span>
                Info
            </span>

            <div class="marquee-viewport relative min-w-0 flex-1 overflow-hidden">
                <ul class="marquee-track flex w-max gap-10 whitespace-nowrap">
                    {{-- Daftar digandakan agar gulungannya menyambung mulus. --}}
                    @foreach ([1, 2] as $putaran)
                        @foreach ($runningAnnouncements as $announcement)
                            <li @if ($putaran === 2) aria-hidden="true" @endif>
                                <a href="{{ route('pengumuman.show', $announcement) }}"
                                   class="text-masjid-50 underline-offset-4 hover:text-white hover:underline"
                                   @if ($putaran === 2) tabindex="-1" @endif>
                                    {{ $announcement->title }}
                                </a>
                            </li>
                        @endforeach
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

<main id="konten" class="mx-auto max-w-6xl px-4 py-8">
    @if (session('status'))
        <div class="mb-6 overflow-hidden rounded-2xl border border-masjid-200 bg-white shadow-sm" role="status">
            <div class="flex gap-3 p-4">
                <span class="mt-0.5 grid h-8 w-8 shrink-0 place-items-center rounded-full bg-masjid-100 text-masjid-700" aria-hidden="true">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                    </svg>
                </span>
                <div>
                    <p class="font-medium text-masjid-900">{{ session('status') }}</p>
                    @if (session('reference'))
                        <p class="mt-1 text-sm text-masjid-600">
                            Nomor referensi Anda:
                            <strong class="font-mono text-masjid-900">{{ session('reference') }}</strong>
                            — simpan sebagai bukti.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @yield('content')
</main>

<footer class="mt-16 border-t border-masjid-100 bg-white">
    <div class="mx-auto grid max-w-6xl gap-8 px-4 py-12 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-masjid-800">{{ $setting->name }}</h2>
            <p class="mt-3 text-sm text-masjid-600">{{ $setting->address }}</p>
            @if ($setting->phone)
                <p class="mt-1 text-sm text-masjid-600">Telp: {{ $setting->phone }}</p>
            @endif
            @if ($setting->email)
                <p class="text-sm text-masjid-600">{{ $setting->email }}</p>
            @endif
        </div>

        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-masjid-800">Informasi</h2>
            <ul class="mt-3 space-y-2 text-sm text-masjid-600">
                <li><a class="transition-colors hover:text-masjid-900" href="{{ route('jadwal-sholat') }}">Jadwal Sholat</a></li>
                <li><a class="transition-colors hover:text-masjid-900" href="{{ route('kajian.index') }}">Kajian &amp; Kegiatan</a></li>
                <li><a class="transition-colors hover:text-masjid-900" href="{{ route('artikel.index') }}">Artikel</a></li>
                <li><a class="transition-colors hover:text-masjid-900" href="{{ route('galeri.index') }}">Galeri</a></li>
                <li><a class="transition-colors hover:text-masjid-900" href="{{ route('e-library') }}">E-Library</a></li>
            </ul>
        </div>

        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-masjid-800">Layanan</h2>
            <ul class="mt-3 space-y-2 text-sm text-masjid-600">
                <li><a class="transition-colors hover:text-masjid-900" href="{{ route('laporan-keuangan') }}">Laporan Keuangan</a></li>
                <li><a class="transition-colors hover:text-masjid-900" href="{{ route('kurban.create') }}">Kurban &amp; Aqiqah</a></li>
                <li><a class="transition-colors hover:text-masjid-900" href="{{ route('zakat.create') }}">Zakat</a></li>
                <li><a class="transition-colors hover:text-masjid-900" href="{{ route('fasilitas.create') }}">Peminjaman Fasilitas</a></li>
                <li><a class="transition-colors hover:text-masjid-900" href="{{ route('saran.create') }}">Kotak Saran</a></li>
            </ul>
        </div>

        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-masjid-800">Ikut Memakmurkan</h2>
            <p class="mt-3 text-sm text-masjid-600">Salurkan infaq dan donasi Anda melalui rekening resmi DKM.</p>
            <a href="{{ route('donasi') }}"
               class="mt-4 inline-flex items-center gap-2 rounded-xl bg-masjid-800 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-masjid-900">
                Cara berdonasi
                <span aria-hidden="true">&rarr;</span>
            </a>
        </div>
    </div>

    <div class="border-t border-masjid-100 px-4 py-5">
        <p class="mx-auto max-w-6xl text-center text-xs text-masjid-500">
            &copy; {{ now()->year }} {{ $setting->name }}. Dikelola oleh Tim DKM.
            <a href="{{ route('filament.admin.pages.dashboard') }}" class="transition-colors hover:text-masjid-800 hover:underline">Masuk panel pengurus</a>
        </p>
    </div>
</footer>

@stack('scripts')
</body>
</html>
