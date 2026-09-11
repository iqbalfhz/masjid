{{-- Hero beranda: identitas masjid + panel jadwal sholat hari ini (PRD 5.1.1). --}}
<section class="bg-mesh-masjid relative -mx-4 overflow-hidden px-4 pb-14 pt-12 text-white sm:rounded-b-[2.5rem] sm:pb-16 lg:rounded-[2rem] lg:px-10 lg:pt-14">
    <div class="pattern-arabesque pointer-events-none absolute inset-0" aria-hidden="true"></div>

    {{-- Bola cahaya dekoratif yang mengambang perlahan. --}}
    <div class="animate-float pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-emas-400/20 blur-3xl" aria-hidden="true"></div>
    <div class="animate-float pointer-events-none absolute -bottom-28 -left-16 h-64 w-64 rounded-full bg-masjid-400/20 blur-3xl [animation-delay:2.5s]" aria-hidden="true"></div>

    <div class="relative grid grid-cols-1 gap-10 lg:grid-cols-[minmax(0,1.05fr)_minmax(0,1fr)] lg:items-center">
        <div data-reveal>
            <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-medium text-emas-100 backdrop-blur-sm">
                <x-public.icon name="building" class="h-3.5 w-3.5" />
                {{ $setting->address }}
            </span>

            <h1 class="mt-5 text-4xl font-semibold leading-[1.1] tracking-tight sm:text-5xl">
                Selamat datang di<br>
                <span class="text-gradient-emas">{{ $setting->name }}</span>
            </h1>

            <p class="mt-4 max-w-xl text-base leading-relaxed text-masjid-100">
                {{ $setting->tagline ?? $setting->description }}
            </p>

            <form action="{{ route('cari') }}" method="get" class="mt-7 max-w-md" role="search">
                <label for="cari-beranda" class="sr-only">Cari kajian, artikel, atau pengumuman</label>
                <div class="group flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 p-1.5 backdrop-blur-md transition-colors focus-within:border-emas-300/60 focus-within:bg-white/15">
                    <input id="cari-beranda" type="search" name="q"
                           placeholder="Cari kajian, artikel, atau pengumuman…"
                           class="w-full border-0 bg-transparent px-3 py-2 text-sm text-white placeholder:text-masjid-200 focus:outline-none focus:ring-0">
                    <button type="submit"
                            class="shrink-0 rounded-xl bg-emas-500 px-4 py-2 text-sm font-semibold text-white transition-all hover:bg-emas-600 hover:shadow-[0_8px_20px_-10px_rgba(215,133,38,0.9)]">
                        Cari
                    </button>
                </div>
            </form>

            <div class="mt-6 flex flex-wrap gap-2 text-sm">
                @foreach ([
                    ['label' => 'Jadwal lengkap', 'url' => route('jadwal-sholat'), 'icon' => 'clock'],
                    ['label' => 'Kajian rutin', 'url' => route('kajian.index'), 'icon' => 'book'],
                    ['label' => 'Laporan keuangan', 'url' => route('laporan-keuangan'), 'icon' => 'banknotes'],
                ] as $pintasan)
                    <a href="{{ $pintasan['url'] }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/10 px-3.5 py-2 font-medium text-masjid-50 backdrop-blur-sm transition-all hover:border-white/25 hover:bg-white/20">
                        <x-public.icon :name="$pintasan['icon']" class="h-4 w-4" />
                        {{ $pintasan['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Panel jadwal sholat --}}
        <div data-reveal style="--reveal-delay: 120ms"
             class="rounded-3xl border border-white/15 bg-white/10 p-6 shadow-[0_24px_60px_-30px_rgba(0,0,0,0.8)] backdrop-blur-xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-emas-200">Jadwal sholat hari ini</h2>
                    <p class="mt-1 text-sm text-masjid-100">{{ today()->translatedFormat('l, d F Y') }}</p>
                </div>
                <x-public.icon name="clock" class="h-6 w-6 shrink-0 text-emas-200" />
            </div>

            @if ($nextPrayer)
                <div data-countdown="{{ $nextPrayer['time']->toIso8601String() }}"
                     class="mt-6 rounded-2xl bg-linear-to-br from-emas-500/25 to-emas-600/10 p-5 ring-1 ring-emas-300/25">
                    <p class="text-xs uppercase tracking-wide text-emas-100">Menuju waktu</p>

                    @php
                        /** Waktu berikutnya bisa jatuh esok hari bila Isya sudah lewat. */
                        $besok = ! $nextPrayer['time']->isToday();
                    @endphp

                    <p class="mt-1 flex flex-wrap items-baseline gap-x-3 text-2xl font-semibold">
                        {{ $nextPrayer['label'] }}
                        <span class="text-base font-normal text-emas-100">
                            {{ $besok ? 'besok ' : '' }}{{ $nextPrayer['time']->format('H:i') }} WIB
                        </span>
                    </p>

                    <div class="mt-4 flex items-end gap-2" role="timer" aria-live="off">
                        @foreach ([
                            ['key' => 'hours', 'label' => 'Jam'],
                            ['key' => 'minutes', 'label' => 'Menit'],
                            ['key' => 'seconds', 'label' => 'Detik'],
                        ] as $index => $unit)
                            @if ($index > 0)
                                <span class="pb-5 text-xl font-light text-emas-200/70" aria-hidden="true">:</span>
                            @endif
                            <div class="flex-1 rounded-xl bg-masjid-950/40 px-2 py-2.5 text-center">
                                <span data-countdown-{{ $unit['key'] }}
                                      class="block font-mono text-2xl font-semibold tabular-nums text-white">--</span>
                                <span class="mt-0.5 block text-[0.65rem] uppercase tracking-wide text-masjid-200">{{ $unit['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($todaySchedule)
                <dl class="mt-5 grid grid-cols-3 gap-2 sm:grid-cols-4">
                    @foreach ($todaySchedule->times() as $key => $time)
                        @php
                            /*
                             * Sorot hanya waktu yang benar-benar dinanti hari ini. Setelah
                             * Isya lewat, waktu berikutnya adalah Subuh besok — sel Subuh
                             * milik jadwal hari ini tidak lagi relevan untuk disorot.
                             */
                            $aktif = $nextPrayer
                                && $nextPrayer['key'] === $key
                                && $nextPrayer['time']->isSameDay($todaySchedule->date);
                        @endphp
                        <div @class([
                            'relative rounded-xl px-2.5 py-2.5 text-center transition-colors',
                            'bg-emas-500 shadow-[0_10px_24px_-14px_rgba(215,133,38,1)]' => $aktif,
                            'bg-white/10' => ! $aktif,
                        ])>
                            @if ($aktif)
                                <span class="animate-pulse-ring absolute inset-0 rounded-xl bg-emas-300" aria-hidden="true"></span>
                            @endif
                            <dt class="relative text-[0.7rem] text-masjid-100">{{ \App\Models\PrayerSchedule::PRAYERS[$key] }}</dt>
                            <dd class="relative font-mono text-base font-semibold tabular-nums">{{ substr($time, 0, 5) }}</dd>
                        </div>
                    @endforeach
                </dl>
            @else
                <p class="mt-5 rounded-xl bg-white/10 p-4 text-sm text-masjid-100">
                    Jadwal hari ini belum tersedia. Pengurus akan segera memperbaruinya.
                </p>
            @endif

            <a href="{{ route('jadwal-sholat') }}"
               class="group mt-5 flex items-center justify-between rounded-xl border border-white/10 px-4 py-3 text-sm font-medium transition-colors hover:border-white/25 hover:bg-white/10">
                Lihat jadwal sebulan penuh &amp; atur pengingat
                <x-public.icon name="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-1" />
            </a>
        </div>
    </div>
</section>
