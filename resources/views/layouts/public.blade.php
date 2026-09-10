<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', $setting->name) — {{ $setting->name }}</title>
    <meta name="description" content="@yield('description', $setting->description ?? $setting->tagline)">

    @if ($setting->logo)
        <link rel="icon" href="{{ Storage::url($setting->logo) }}">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-masjid-50 font-sans text-masjid-950 antialiased">

<a href="#konten" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:text-masjid-700 focus:shadow">
    Lompat ke konten utama
</a>

<header class="sticky top-0 z-40 border-b border-masjid-100 bg-white/95 backdrop-blur">
    <div class="mx-auto flex max-w-6xl items-center gap-4 px-4 py-3">
        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-3">
            @if ($setting->logo)
                <img src="{{ Storage::url($setting->logo) }}" alt="" class="h-10 w-10 rounded-full object-cover">
            @else
                <span class="grid h-10 w-10 place-items-center rounded-full bg-masjid-600 text-lg font-semibold text-white" aria-hidden="true">ﷺ</span>
            @endif
            <span class="leading-tight">
                <span class="block text-base font-semibold text-masjid-800">{{ $setting->name }}</span>
                <span class="block text-xs text-masjid-600">{{ $setting->address }}</span>
            </span>
        </a>

        <nav class="ms-auto hidden items-center gap-1 lg:flex" aria-label="Navigasi utama">
            @foreach ($navigation as $item)
                <a href="{{ $item['url'] }}"
                   @class([
                       'rounded-lg px-3 py-2 text-sm font-medium transition',
                       'bg-masjid-100 text-masjid-800' => $item['active'],
                       'text-masjid-700 hover:bg-masjid-50' => ! $item['active'],
                   ])
                   @if ($item['active']) aria-current="page" @endif>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <a href="{{ route('donasi') }}" class="ms-auto rounded-lg bg-emas-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emas-600 lg:ms-0">
            Donasi
        </a>

        <button type="button"
                class="rounded-lg p-2 text-masjid-700 hover:bg-masjid-50 lg:hidden"
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
                           'block rounded-lg px-3 py-2 text-sm font-medium',
                           'bg-masjid-100 text-masjid-800' => $item['active'],
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
    <div class="bg-masjid-800 text-white">
        <div class="mx-auto flex max-w-6xl items-center gap-3 px-4 py-2 text-sm">
            <span class="shrink-0 rounded bg-emas-500 px-2 py-0.5 text-xs font-semibold uppercase tracking-wide">Info</span>
            <div class="min-w-0 flex-1 overflow-hidden">
                <ul class="flex animate-none gap-8 overflow-x-auto whitespace-nowrap [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                    @foreach ($runningAnnouncements as $announcement)
                        <li class="shrink-0">
                            <a href="{{ route('pengumuman.show', $announcement) }}" class="hover:underline">{{ $announcement->title }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

<main id="konten" class="mx-auto max-w-6xl px-4 py-8">
    @if (session('status'))
        <div class="mb-6 rounded-xl border border-masjid-200 bg-white p-4 text-masjid-800 shadow-sm" role="status">
            <p class="font-medium">{{ session('status') }}</p>
            @if (session('reference'))
                <p class="mt-1 text-sm">Nomor referensi Anda: <strong class="font-mono">{{ session('reference') }}</strong> — simpan sebagai bukti.</p>
            @endif
        </div>
    @endif

    @yield('content')
</main>

<footer class="mt-12 border-t border-masjid-100 bg-white">
    <div class="mx-auto grid max-w-6xl gap-8 px-4 py-10 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-masjid-800">{{ $setting->name }}</h2>
            <p class="mt-2 text-sm text-masjid-600">{{ $setting->address }}</p>
            @if ($setting->phone)
                <p class="mt-1 text-sm text-masjid-600">Telp: {{ $setting->phone }}</p>
            @endif
            @if ($setting->email)
                <p class="text-sm text-masjid-600">{{ $setting->email }}</p>
            @endif
        </div>

        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-masjid-800">Informasi</h2>
            <ul class="mt-2 space-y-1 text-sm text-masjid-600">
                <li><a class="hover:text-masjid-800 hover:underline" href="{{ route('jadwal-sholat') }}">Jadwal Sholat</a></li>
                <li><a class="hover:text-masjid-800 hover:underline" href="{{ route('kajian.index') }}">Kajian & Kegiatan</a></li>
                <li><a class="hover:text-masjid-800 hover:underline" href="{{ route('artikel.index') }}">Artikel</a></li>
                <li><a class="hover:text-masjid-800 hover:underline" href="{{ route('galeri.index') }}">Galeri</a></li>
                <li><a class="hover:text-masjid-800 hover:underline" href="{{ route('e-library') }}">E-Library</a></li>
            </ul>
        </div>

        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-masjid-800">Layanan</h2>
            <ul class="mt-2 space-y-1 text-sm text-masjid-600">
                <li><a class="hover:text-masjid-800 hover:underline" href="{{ route('laporan-keuangan') }}">Laporan Keuangan</a></li>
                <li><a class="hover:text-masjid-800 hover:underline" href="{{ route('kurban.create') }}">Kurban & Aqiqah</a></li>
                <li><a class="hover:text-masjid-800 hover:underline" href="{{ route('zakat.create') }}">Zakat</a></li>
                <li><a class="hover:text-masjid-800 hover:underline" href="{{ route('fasilitas.create') }}">Peminjaman Fasilitas</a></li>
                <li><a class="hover:text-masjid-800 hover:underline" href="{{ route('saran.create') }}">Kotak Saran</a></li>
            </ul>
        </div>

        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-masjid-800">Ikut Memakmurkan</h2>
            <p class="mt-2 text-sm text-masjid-600">Salurkan infaq dan donasi Anda melalui rekening resmi DKM.</p>
            <a href="{{ route('donasi') }}" class="mt-3 inline-block rounded-lg bg-masjid-600 px-4 py-2 text-sm font-semibold text-white hover:bg-masjid-700">
                Cara berdonasi
            </a>
        </div>
    </div>

    <div class="border-t border-masjid-100 px-4 py-4">
        <p class="mx-auto max-w-6xl text-center text-xs text-masjid-500">
            &copy; {{ now()->year }} {{ $setting->name }}. Dikelola oleh Tim DKM.
            <a href="{{ route('filament.admin.pages.dashboard') }}" class="hover:underline">Masuk panel pengurus</a>
        </p>
    </div>
</footer>

@stack('scripts')
</body>
</html>
