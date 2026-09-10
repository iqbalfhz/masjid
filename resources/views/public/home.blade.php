@extends('layouts.public')

@section('title', 'Beranda')
@section('description', 'Jadwal sholat, kajian, pengumuman, dan laporan keuangan Masjid An-Nur Tangcity Mall.')

@section('content')
    <section class="mb-10 overflow-hidden rounded-3xl bg-masjid-800 text-white">
        <div class="grid gap-8 p-6 sm:p-10 lg:grid-cols-[1.1fr,1fr]">
            <div>
                <p class="text-sm font-medium uppercase tracking-wide text-emas-300">{{ $setting->address }}</p>
                <h1 class="mt-2 text-3xl font-semibold sm:text-4xl">{{ $setting->name }}</h1>
                <p class="mt-3 max-w-xl text-masjid-100">{{ $setting->tagline ?? $setting->description }}</p>

                <form action="{{ route('cari') }}" method="get" class="mt-6 flex max-w-md gap-2" role="search">
                    <label for="cari-beranda" class="sr-only">Cari kajian, artikel, pengumuman</label>
                    <input id="cari-beranda" type="search" name="q" placeholder="Cari kajian, artikel, atau pengumuman…"
                           class="w-full rounded-lg border-0 px-4 py-2.5 text-sm text-masjid-900 placeholder:text-masjid-400 focus:ring-2 focus:ring-emas-400">
                    <button type="submit" class="rounded-lg bg-emas-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emas-600">Cari</button>
                </form>

                <div class="mt-6 flex flex-wrap gap-2 text-sm">
                    <a href="{{ route('jadwal-sholat') }}" class="rounded-lg bg-white/10 px-3 py-1.5 hover:bg-white/20">Jadwal lengkap</a>
                    <a href="{{ route('kajian.index') }}" class="rounded-lg bg-white/10 px-3 py-1.5 hover:bg-white/20">Kajian rutin</a>
                    <a href="{{ route('laporan-keuangan') }}" class="rounded-lg bg-white/10 px-3 py-1.5 hover:bg-white/20">Laporan keuangan</a>
                </div>
            </div>

            <div class="rounded-2xl bg-white/10 p-5 backdrop-blur">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-emas-300">Jadwal sholat hari ini</h2>
                <p class="mt-1 text-sm text-masjid-100">{{ today()->translatedFormat('l, d F Y') }}</p>

                @if ($nextPrayer)
                    <p class="mt-4 text-sm text-masjid-100">Menuju</p>
                    <p class="text-2xl font-semibold">{{ $nextPrayer['label'] }} &middot; {{ $nextPrayer['time']->format('H:i') }} WIB</p>
                    <p class="mt-1 text-sm text-emas-200" data-countdown="{{ $nextPrayer['time']->toIso8601String() }}">
                        {{ now()->diffForHumans($nextPrayer['time'], ['parts' => 2, 'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE]) }} lagi
                    </p>
                @endif

                @if ($todaySchedule)
                    <dl class="mt-5 grid grid-cols-2 gap-2 text-sm sm:grid-cols-3">
                        @foreach ($todaySchedule->times() as $key => $time)
                            <div @class([
                                'rounded-lg px-3 py-2',
                                'bg-emas-500/90 font-semibold' => $nextPrayer && $nextPrayer['key'] === $key,
                                'bg-white/10' => ! ($nextPrayer && $nextPrayer['key'] === $key),
                            ])>
                                <dt class="text-xs text-masjid-100">{{ \App\Models\PrayerSchedule::PRAYERS[$key] }}</dt>
                                <dd class="text-base">{{ substr($time, 0, 5) }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @else
                    <p class="mt-4 rounded-lg bg-white/10 p-3 text-sm">Jadwal hari ini belum tersedia. Pengurus akan segera memperbaruinya.</p>
                @endif
            </div>
        </div>
    </section>

    @if ($statistics)
        <section class="mb-10">
            <h2 class="sr-only">Statistik pencapaian masjid</h2>
            <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($statistics as $stat)
                    <x-public.card>
                        <dt class="text-sm text-masjid-600">{{ $stat['label'] }}</dt>
                        <dd class="mt-1 text-xl font-semibold text-masjid-900">{{ $stat['value'] }}</dd>
                        <p class="mt-1 text-xs text-masjid-500">{{ $stat['caption'] }}</p>
                    </x-public.card>
                @endforeach
            </dl>
        </section>
    @endif

    <div class="grid gap-10 lg:grid-cols-[2fr,1fr]">
        <div class="space-y-10">
            <section>
                <x-public.section-heading title="Kajian rutin" :href="route('kajian.index')" />

                @if ($upcomingStudies->isEmpty())
                    <x-public.empty-state message="Belum ada kajian yang dijadwalkan." />
                @else
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach ($upcomingStudies as $study)
                            <x-public.card as="article">
                                <h3 class="font-semibold text-masjid-900">
                                    <a href="{{ route('kajian.show', $study) }}" class="hover:underline">{{ $study->theme }}</a>
                                </h3>
                                <p class="mt-1 text-sm text-masjid-600">{{ $study->ustadz_name }}</p>
                                <p class="mt-2 text-sm text-masjid-700">{{ $study->scheduleLabel() }}</p>
                                @if ($study->rsvp_enabled)
                                    <x-public.badge color="emas" class="mt-3">Terbuka RSVP</x-public.badge>
                                @endif
                            </x-public.card>
                        @endforeach
                    </div>
                @endif
            </section>

            <section>
                <x-public.section-heading title="Kegiatan terdekat" :href="route('kegiatan.index')" />

                @if ($upcomingEvents->isEmpty())
                    <x-public.empty-state message="Belum ada kegiatan yang dijadwalkan." />
                @else
                    <div class="grid gap-4 sm:grid-cols-3">
                        @foreach ($upcomingEvents as $event)
                            <x-public.card as="article" class="flex flex-col">
                                <p class="text-xs font-semibold uppercase tracking-wide text-emas-600">
                                    {{ $event->event_date->translatedFormat('d M Y') }}
                                </p>
                                <h3 class="mt-1 font-semibold text-masjid-900">
                                    <a href="{{ route('kegiatan.show', $event) }}" class="hover:underline">{{ $event->title }}</a>
                                </h3>
                                @if ($event->category)
                                    <x-public.badge class="mt-3 self-start">{{ $event->category }}</x-public.badge>
                                @endif
                            </x-public.card>
                        @endforeach
                    </div>
                @endif
            </section>

            <section>
                <x-public.section-heading title="Artikel terbaru" :href="route('artikel.index')" />

                @if ($latestArticles->isEmpty())
                    <x-public.empty-state message="Belum ada artikel yang tayang." />
                @else
                    <div class="grid gap-4 sm:grid-cols-3">
                        @foreach ($latestArticles as $article)
                            <x-public.card as="article" class="flex flex-col overflow-hidden !p-0">
                                <img src="{{ $article->cover_image ? Storage::url($article->cover_image) : asset('images/placeholder.svg') }}"
                                     alt="" class="h-32 w-full object-cover">
                                <div class="p-4">
                                    @if ($article->category)
                                        <x-public.badge color="gray">{{ $article->category->name }}</x-public.badge>
                                    @endif
                                    <h3 class="mt-2 font-semibold text-masjid-900">
                                        <a href="{{ route('artikel.show', $article) }}" class="hover:underline">{{ $article->title }}</a>
                                    </h3>
                                    <p class="mt-1 text-sm text-masjid-600">{{ Str::limit($article->excerpt ?? strip_tags($article->content), 90) }}</p>
                                </div>
                            </x-public.card>
                        @endforeach
                    </div>
                @endif
            </section>

            <section>
                <x-public.section-heading title="Galeri kegiatan" :href="route('galeri.index')" />

                @if ($latestAlbums->isEmpty())
                    <x-public.empty-state message="Belum ada album galeri." />
                @else
                    <div class="grid gap-4 sm:grid-cols-3">
                        @foreach ($latestAlbums as $album)
                            <a href="{{ route('galeri.show', $album) }}" class="group overflow-hidden rounded-2xl border border-masjid-100 bg-white shadow-sm">
                                <img src="{{ $album->cover_image ? Storage::url($album->cover_image) : ($album->items->first()?->url() ?? asset('images/placeholder.svg')) }}"
                                     alt="" class="h-36 w-full object-cover transition group-hover:scale-105">
                                <div class="p-4">
                                    <h3 class="font-semibold text-masjid-900 group-hover:underline">{{ $album->title }}</h3>
                                    <p class="mt-1 text-xs text-masjid-500">{{ $album->items->count() }} foto &middot; {{ $album->event_date?->translatedFormat('F Y') }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        <aside class="space-y-8">
            <section>
                <x-public.section-heading title="Pengumuman" />

                @if ($announcements->isEmpty())
                    <x-public.empty-state message="Tidak ada pengumuman aktif." />
                @else
                    <ul class="space-y-3">
                        @foreach ($announcements as $announcement)
                            <li>
                                <x-public.card as="article">
                                    <p class="text-xs text-masjid-500">{{ $announcement->start_date->translatedFormat('d F Y') }}</p>
                                    <h3 class="mt-1 font-semibold text-masjid-900">
                                        <a href="{{ route('pengumuman.show', $announcement) }}" class="hover:underline">{{ $announcement->title }}</a>
                                    </h3>
                                    <p class="mt-1 text-sm text-masjid-600">{{ Str::limit(strip_tags($announcement->content), 110) }}</p>
                                </x-public.card>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section>
                <x-public.section-heading title="Keuangan bulan ini" :href="route('laporan-keuangan')" linkLabel="Laporan lengkap" />

                <x-public.card>
                    <dl class="space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <dt class="text-masjid-600">Pemasukan</dt>
                            <dd class="font-semibold text-masjid-800">Rp {{ number_format($monthlyIncome, 0, ',', '.') }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-masjid-600">Pengeluaran</dt>
                            <dd class="font-semibold text-masjid-800">Rp {{ number_format($monthlyExpense, 0, ',', '.') }}</dd>
                        </div>
                        <div class="flex items-center justify-between border-t border-masjid-100 pt-3">
                            <dt class="font-medium text-masjid-700">Saldo</dt>
                            <dd class="text-base font-semibold {{ $monthlyIncome - $monthlyExpense >= 0 ? 'text-masjid-700' : 'text-red-600' }}">
                                Rp {{ number_format($monthlyIncome - $monthlyExpense, 0, ',', '.') }}
                            </dd>
                        </div>
                    </dl>
                    <p class="mt-3 text-xs text-masjid-500">Periode {{ today()->translatedFormat('F Y') }}</p>
                </x-public.card>
            </section>

            @if ($testimonials->isNotEmpty())
                <section>
                    <x-public.section-heading title="Kata jamaah" :href="route('testimoni.index')" />

                    <ul class="space-y-3">
                        @foreach ($testimonials as $testimonial)
                            <li>
                                <x-public.card as="blockquote">
                                    <p class="text-sm text-masjid-700">&ldquo;{{ Str::limit($testimonial->message, 140) }}&rdquo;</p>
                                    <footer class="mt-2 text-xs font-medium text-masjid-500">— {{ $testimonial->displayName() }}</footer>
                                </x-public.card>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <section>
                <x-public.card class="bg-masjid-800 text-white">
                    <h2 class="font-semibold">Layanan jamaah</h2>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li><a href="{{ route('kurban.create') }}" class="hover:underline">Pendaftaran kurban &amp; aqiqah</a></li>
                        <li><a href="{{ route('zakat.create') }}" class="hover:underline">Pendaftaran zakat</a></li>
                        <li><a href="{{ route('fasilitas.create') }}" class="hover:underline">Peminjaman fasilitas</a></li>
                        <li><a href="{{ route('saran.create') }}" class="hover:underline">Kotak saran &amp; pengaduan</a></li>
                        <li><a href="{{ route('e-library') }}" class="hover:underline">Arsip materi kajian</a></li>
                    </ul>
                </x-public.card>
            </section>
        </aside>
    </div>
@endsection
