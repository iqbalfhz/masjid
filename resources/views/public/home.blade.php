@extends('layouts.public')

@section('title', 'Beranda')
@section('description', 'Jadwal sholat, kajian, pengumuman, dan laporan keuangan Masjid An-Nur Tangcity Mall.')

@section('content')
    @include('public.partials.hero')

    {{-- Statistik pencapaian (PRD 5.1.16) --}}
    <section class="mt-12" aria-labelledby="judul-statistik">
        <h2 id="judul-statistik" class="sr-only">Statistik pencapaian masjid</h2>

        <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($statistics as $index => $stat)
                <div data-reveal style="--reveal-delay: {{ $index * 90 }}ms"
                     class="card-lift group rounded-2xl border border-masjid-100 bg-white p-5 shadow-sm">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-masjid-50 text-masjid-600 transition-colors group-hover:bg-masjid-600 group-hover:text-white">
                        <x-public.icon :name="$stat['icon']" />
                    </span>

                    <dd class="mt-4 text-2xl font-semibold tracking-tight text-masjid-900"
                        data-count-to="{{ $stat['value'] }}"
                        data-count-prefix="{{ $stat['prefix'] }}"
                        data-count-suffix="{{ $stat['suffix'] }}">{{ $stat['display'] }}</dd>

                    <dt class="mt-1 text-sm font-medium text-masjid-700">{{ $stat['label'] }}</dt>
                    <p class="mt-0.5 text-xs text-masjid-500">{{ $stat['caption'] }}</p>
                </div>
            @endforeach
        </dl>
    </section>

    <div class="mt-16 grid gap-12 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        <div class="space-y-14">
            {{-- Kajian rutin --}}
            <section data-reveal aria-labelledby="judul-kajian">
                <div class="mb-5 flex items-end justify-between gap-4">
                    <div>
                        <h2 id="judul-kajian" class="text-xl font-semibold tracking-tight text-masjid-900">Kajian rutin</h2>
                        <p class="mt-1 text-sm text-masjid-600">Majelis ilmu yang berjalan tiap pekan.</p>
                    </div>
                    <a href="{{ route('kajian.index') }}"
                       class="group inline-flex shrink-0 items-center gap-1.5 text-sm font-medium text-masjid-600 transition-colors hover:text-masjid-900">
                        Lihat semua
                        <x-public.icon name="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-1" />
                    </a>
                </div>

                @if ($upcomingStudies->isEmpty())
                    <x-public.empty-state message="Belum ada kajian yang dijadwalkan." />
                @else
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach ($upcomingStudies as $study)
                            <article class="card-lift group relative rounded-2xl border border-masjid-100 bg-white p-5 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="font-semibold leading-snug text-masjid-900">
                                        <a href="{{ route('kajian.show', $study) }}" class="after:absolute after:inset-0">
                                            {{ $study->theme }}
                                        </a>
                                    </h3>
                                    @if ($study->rsvp_enabled)
                                        <span class="shrink-0 rounded-full bg-emas-100 px-2.5 py-0.5 text-xs font-medium text-emas-800">RSVP</span>
                                    @endif
                                </div>

                                <p class="mt-1.5 text-sm text-masjid-600">{{ $study->ustadz_name }}</p>

                                <p class="mt-3 inline-flex items-center gap-1.5 text-sm font-medium text-masjid-700">
                                    <x-public.icon name="clock" class="h-4 w-4 text-masjid-400" />
                                    {{ $study->scheduleLabel() }}
                                </p>

                                <span class="accent-underline block"></span>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- Kegiatan terdekat --}}
            <section data-reveal aria-labelledby="judul-kegiatan">
                <div class="mb-5 flex items-end justify-between gap-4">
                    <div>
                        <h2 id="judul-kegiatan" class="text-xl font-semibold tracking-tight text-masjid-900">Kegiatan terdekat</h2>
                        <p class="mt-1 text-sm text-masjid-600">Agenda yang akan segera berlangsung.</p>
                    </div>
                    <a href="{{ route('kegiatan.index') }}"
                       class="group inline-flex shrink-0 items-center gap-1.5 text-sm font-medium text-masjid-600 transition-colors hover:text-masjid-900">
                        Lihat semua
                        <x-public.icon name="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-1" />
                    </a>
                </div>

                @if ($upcomingEvents->isEmpty())
                    <x-public.empty-state message="Belum ada kegiatan yang dijadwalkan." />
                @else
                    <ul class="space-y-3">
                        @foreach ($upcomingEvents as $event)
                            <li>
                                <article class="card-lift group relative flex items-center gap-5 rounded-2xl border border-masjid-100 bg-white p-4 shadow-sm sm:p-5">
                                    {{-- Tanggal sebagai "sobekan kalender" --}}
                                    <div class="grid w-16 shrink-0 place-items-center rounded-xl bg-linear-to-br from-masjid-700 to-masjid-900 py-2.5 text-white">
                                        <span class="text-xl font-semibold leading-none">{{ $event->event_date->format('d') }}</span>
                                        <span class="mt-1 text-[0.65rem] uppercase tracking-wide text-masjid-200">
                                            {{ $event->event_date->translatedFormat('M') }}
                                        </span>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <h3 class="truncate font-semibold text-masjid-900">
                                            <a href="{{ route('kegiatan.show', $event) }}" class="after:absolute after:inset-0">
                                                {{ $event->title }}
                                            </a>
                                        </h3>
                                        <p class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-masjid-600">
                                            <span>{{ $event->event_date->translatedFormat('l') }}</span>
                                            @if ($event->category)
                                                <span class="rounded-full bg-masjid-50 px-2 py-0.5 text-xs font-medium text-masjid-700">
                                                    {{ $event->category }}
                                                </span>
                                            @endif
                                        </p>
                                    </div>

                                    <x-public.icon name="arrow-right"
                                                   class="h-5 w-5 shrink-0 text-masjid-300 transition-all group-hover:translate-x-1 group-hover:text-masjid-600" />
                                </article>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            {{-- Artikel terbaru --}}
            <section data-reveal aria-labelledby="judul-artikel">
                <div class="mb-5 flex items-end justify-between gap-4">
                    <div>
                        <h2 id="judul-artikel" class="text-xl font-semibold tracking-tight text-masjid-900">Artikel terbaru</h2>
                        <p class="mt-1 text-sm text-masjid-600">Bacaan ringan penambah ilmu.</p>
                    </div>
                    <a href="{{ route('artikel.index') }}"
                       class="group inline-flex shrink-0 items-center gap-1.5 text-sm font-medium text-masjid-600 transition-colors hover:text-masjid-900">
                        Lihat semua
                        <x-public.icon name="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-1" />
                    </a>
                </div>

                @if ($latestArticles->isEmpty())
                    <x-public.empty-state message="Belum ada artikel yang tayang." />
                @else
                    <div class="grid gap-4 sm:grid-cols-3">
                        @foreach ($latestArticles as $article)
                            <article class="card-lift group relative flex flex-col overflow-hidden rounded-2xl border border-masjid-100 bg-white shadow-sm">
                                <div class="aspect-16/10 overflow-hidden bg-masjid-50">
                                    <x-public.image :src="$article->cover_image ? Storage::url($article->cover_image) : null"
                                                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                                </div>

                                <div class="flex flex-1 flex-col p-4">
                                    @if ($article->category)
                                        <span class="self-start rounded-full bg-masjid-50 px-2.5 py-0.5 text-xs font-medium text-masjid-700">
                                            {{ $article->category->name }}
                                        </span>
                                    @endif

                                    <h3 class="mt-2 font-semibold leading-snug text-masjid-900">
                                        <a href="{{ route('artikel.show', $article) }}" class="after:absolute after:inset-0">
                                            {{ $article->title }}
                                        </a>
                                    </h3>

                                    <p class="mt-1.5 flex-1 text-sm text-masjid-600">
                                        {{ Str::limit($article->excerpt ?? strip_tags($article->content), 90) }}
                                    </p>

                                    <p class="mt-3 text-xs text-masjid-500">
                                        {{ $article->publish_date->translatedFormat('d F Y') }}
                                    </p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- Galeri --}}
            <section data-reveal aria-labelledby="judul-galeri">
                <div class="mb-5 flex items-end justify-between gap-4">
                    <div>
                        <h2 id="judul-galeri" class="text-xl font-semibold tracking-tight text-masjid-900">Galeri kegiatan</h2>
                        <p class="mt-1 text-sm text-masjid-600">Dokumentasi kebersamaan jamaah.</p>
                    </div>
                    <a href="{{ route('galeri.index') }}"
                       class="group inline-flex shrink-0 items-center gap-1.5 text-sm font-medium text-masjid-600 transition-colors hover:text-masjid-900">
                        Lihat semua
                        <x-public.icon name="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-1" />
                    </a>
                </div>

                @if ($latestAlbums->isEmpty())
                    <x-public.empty-state message="Belum ada album galeri." />
                @else
                    <div class="grid gap-4 sm:grid-cols-3">
                        @foreach ($latestAlbums as $album)
                            <a href="{{ route('galeri.show', $album) }}"
                               class="group relative block overflow-hidden rounded-2xl shadow-sm">
                                <div class="aspect-4/3 overflow-hidden bg-masjid-100">
                                    <x-public.image :src="$album->cover_image ? Storage::url($album->cover_image) : $album->items->first()?->url()"
                                                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" />
                                </div>

                                {{-- Gradasi agar teks tetap terbaca di atas foto apa pun. --}}
                                <div class="absolute inset-0 bg-linear-to-t from-masjid-950/85 via-masjid-950/25 to-transparent"></div>

                                <div class="absolute inset-x-0 bottom-0 p-4 text-white">
                                    <h3 class="font-semibold leading-snug">{{ $album->title }}</h3>
                                    <p class="mt-0.5 text-xs text-masjid-200">
                                        {{ $album->items->count() }} dokumentasi
                                        @if ($album->event_date)
                                            &middot; {{ $album->event_date->translatedFormat('F Y') }}
                                        @endif
                                    </p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        {{-- Kolom samping --}}
        <aside class="space-y-8">
            {{-- Pengumuman --}}
            <section data-reveal aria-labelledby="judul-pengumuman">
                <h2 id="judul-pengumuman" class="mb-4 text-lg font-semibold tracking-tight text-masjid-900">Pengumuman</h2>

                @if ($announcements->isEmpty())
                    <x-public.empty-state message="Tidak ada pengumuman aktif." />
                @else
                    <ul class="space-y-3">
                        @foreach ($announcements as $announcement)
                            <li>
                                <article class="card-lift group relative rounded-2xl border border-masjid-100 bg-white p-4 shadow-sm">
                                    <div class="flex items-center gap-2 text-xs">
                                        @if ($announcement->priority === \App\Enums\AnnouncementPriority::Tinggi)
                                            <span class="rounded-full bg-emas-100 px-2 py-0.5 font-semibold text-emas-800">Penting</span>
                                        @endif
                                        <span class="text-masjid-500">{{ $announcement->start_date->translatedFormat('d F Y') }}</span>
                                    </div>

                                    <h3 class="mt-1.5 font-semibold leading-snug text-masjid-900">
                                        <a href="{{ route('pengumuman.show', $announcement) }}" class="after:absolute after:inset-0">
                                            {{ $announcement->title }}
                                        </a>
                                    </h3>

                                    <p class="mt-1 text-sm text-masjid-600">
                                        {{ Str::limit(strip_tags($announcement->content), 100) }}
                                    </p>
                                </article>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            {{-- Ringkasan keuangan --}}
            <section data-reveal aria-labelledby="judul-keuangan">
                <div class="mb-4 flex items-end justify-between gap-4">
                    <h2 id="judul-keuangan" class="text-lg font-semibold tracking-tight text-masjid-900">Keuangan bulan ini</h2>
                    <a href="{{ route('laporan-keuangan') }}" class="shrink-0 text-sm font-medium text-masjid-600 transition-colors hover:text-masjid-900">
                        Detail
                    </a>
                </div>

                @php
                    $saldo = $monthlyIncome - $monthlyExpense;
                    $totalArus = max($monthlyIncome + $monthlyExpense, 1);
                @endphp

                <div class="rounded-2xl border border-masjid-100 bg-white p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-wide text-masjid-500">Saldo {{ today()->translatedFormat('F Y') }}</p>
                    <p class="mt-1 text-2xl font-semibold tracking-tight {{ $saldo >= 0 ? 'text-masjid-900' : 'text-red-600' }}">
                        Rp {{ number_format($saldo, 0, ',', '.') }}
                    </p>

                    {{-- Bar perbandingan pemasukan vs pengeluaran --}}
                    <div class="mt-4 flex h-2 overflow-hidden rounded-full bg-masjid-100" aria-hidden="true">
                        <div class="bg-masjid-500" style="width: {{ round($monthlyIncome / $totalArus * 100, 2) }}%"></div>
                        <div class="bg-emas-500" style="width: {{ round($monthlyExpense / $totalArus * 100, 2) }}%"></div>
                    </div>

                    <dl class="mt-4 space-y-2.5 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="inline-flex items-center gap-2 text-masjid-600">
                                <span class="h-2 w-2 rounded-full bg-masjid-500" aria-hidden="true"></span>
                                Pemasukan
                            </dt>
                            <dd class="font-semibold tabular-nums text-masjid-900">Rp {{ number_format($monthlyIncome, 0, ',', '.') }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="inline-flex items-center gap-2 text-masjid-600">
                                <span class="h-2 w-2 rounded-full bg-emas-500" aria-hidden="true"></span>
                                Pengeluaran
                            </dt>
                            <dd class="font-semibold tabular-nums text-masjid-900">Rp {{ number_format($monthlyExpense, 0, ',', '.') }}</dd>
                        </div>
                    </dl>
                </div>
            </section>

            {{-- Testimoni --}}
            @if ($testimonials->isNotEmpty())
                <section data-reveal aria-labelledby="judul-testimoni">
                    <div class="mb-4 flex items-end justify-between gap-4">
                        <h2 id="judul-testimoni" class="text-lg font-semibold tracking-tight text-masjid-900">Kata jamaah</h2>
                        <a href="{{ route('testimoni.index') }}" class="shrink-0 text-sm font-medium text-masjid-600 transition-colors hover:text-masjid-900">
                            Semua
                        </a>
                    </div>

                    <ul class="space-y-3">
                        @foreach ($testimonials as $testimonial)
                            <li>
                                <figure class="rounded-2xl border border-masjid-100 bg-white p-4 shadow-sm">
                                    <x-public.icon name="chat" class="h-5 w-5 text-masjid-300" />
                                    <blockquote class="mt-2 text-sm leading-relaxed text-masjid-700">
                                        {{ Str::limit($testimonial->message, 140) }}
                                    </blockquote>
                                    <figcaption class="mt-3 text-xs font-medium text-masjid-500">
                                        — {{ $testimonial->displayName() }}
                                    </figcaption>
                                </figure>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            {{-- Layanan jamaah --}}
            <section data-reveal aria-labelledby="judul-layanan">
                <div class="bg-mesh-masjid relative overflow-hidden rounded-2xl p-5 text-white shadow-sm">
                    <div class="pattern-arabesque pointer-events-none absolute inset-0" aria-hidden="true"></div>

                    <div class="relative">
                        <h2 id="judul-layanan" class="text-lg font-semibold">Layanan jamaah</h2>
                        <p class="mt-1 text-sm text-masjid-100">Semua bisa diajukan online, tanpa perlu datang dua kali.</p>

                        <ul class="mt-4 space-y-1.5">
                            @foreach ([
                                ['label' => 'Pendaftaran kurban & aqiqah', 'url' => route('kurban.create'), 'icon' => 'gift'],
                                ['label' => 'Pendaftaran zakat', 'url' => route('zakat.create'), 'icon' => 'hand'],
                                ['label' => 'Peminjaman fasilitas', 'url' => route('fasilitas.create'), 'icon' => 'building'],
                                ['label' => 'Kotak saran & pengaduan', 'url' => route('saran.create'), 'icon' => 'chat'],
                                ['label' => 'Arsip materi kajian', 'url' => route('e-library'), 'icon' => 'document'],
                            ] as $layanan)
                                <li>
                                    <a href="{{ $layanan['url'] }}"
                                       class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors hover:bg-white/10">
                                        <x-public.icon :name="$layanan['icon']" class="h-4 w-4 shrink-0 text-emas-200" />
                                        <span class="flex-1">{{ $layanan['label'] }}</span>
                                        <x-public.icon name="arrow-right"
                                                       class="h-4 w-4 shrink-0 text-masjid-200 opacity-0 transition-all group-hover:translate-x-0.5 group-hover:opacity-100" />
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </section>
        </aside>
    </div>

    {{-- Ajakan donasi penutup --}}
    <section data-reveal class="mt-16" aria-labelledby="judul-ajakan">
        <div class="relative overflow-hidden rounded-3xl border border-masjid-100 bg-white p-8 text-center shadow-sm sm:p-12">
            <div class="pointer-events-none absolute inset-x-0 -top-24 mx-auto h-48 w-104 rounded-full bg-emas-200/40 blur-3xl" aria-hidden="true"></div>

            <div class="relative">
                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-linear-to-br from-emas-400 to-emas-600 text-white mx-auto shadow-sm">
                    <x-public.icon name="banknotes" class="h-6 w-6" />
                </span>

                <h2 id="judul-ajakan" class="mt-5 text-2xl font-semibold tracking-tight text-masjid-900">
                    Mari bersama memakmurkan masjid
                </h2>
                <p class="mx-auto mt-3 max-w-lg text-masjid-600">
                    Setiap infaq yang Anda titipkan dicatat dan dilaporkan terbuka —
                    bisa Anda periksa sendiri kapan saja di halaman laporan keuangan.
                </p>

                <div class="mt-7 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('donasi') }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-linear-to-br from-emas-400 to-emas-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition-all hover:shadow-[0_14px_30px_-14px_rgba(190,103,30,0.9)] hover:brightness-105">
                        Cara berdonasi
                        <x-public.icon name="arrow-right" class="h-4 w-4" />
                    </a>
                    <a href="{{ route('laporan-keuangan') }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-masjid-200 px-6 py-3 text-sm font-semibold text-masjid-800 transition-colors hover:bg-masjid-50">
                        Lihat laporan keuangan
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
