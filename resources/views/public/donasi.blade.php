@extends('layouts.public')

@section('title', 'Donasi & Infaq')
@section('description', 'Salurkan infaq dan donasi Anda ke Masjid An-Nur melalui rekening resmi DKM atau QRIS.')

@section('content')
    <x-public.page-header
        title="Donasi & Infaq"
        subtitle="Setiap rupiah yang Anda titipkan dicatat dan dilaporkan terbuka pada halaman Laporan Keuangan." />

    <div class="grid gap-8 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)]">
        <div class="space-y-6">
            <x-public.card>
                <h2 class="font-semibold text-masjid-900">Transfer bank</h2>

                @if ($setting->bank_account_number)
                    <dl class="mt-4 space-y-3 text-sm">
                        <div>
                            <dt class="text-masjid-500">Bank</dt>
                            <dd class="font-medium text-masjid-900">{{ $setting->bank_name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-masjid-500">Nomor rekening</dt>
                            <dd class="mt-1 flex flex-wrap items-center gap-3">
                                <span class="font-mono text-lg font-semibold tracking-wide text-masjid-900" id="nomor-rekening">
                                    {{ $setting->bank_account_number }}
                                </span>
                                <button type="button"
                                        class="rounded-lg border border-masjid-200 px-3 py-1.5 text-xs font-medium text-masjid-700 hover:bg-masjid-50"
                                        data-copy-target="nomor-rekening">
                                    Salin nomor rekening
                                </button>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-masjid-500">Atas nama</dt>
                            <dd class="font-medium text-masjid-900">{{ $setting->bank_account_name ?? '—' }}</dd>
                        </div>
                    </dl>
                @else
                    <p class="mt-2 text-sm text-masjid-600">Informasi rekening sedang diperbarui pengurus.</p>
                @endif
            </x-public.card>

            @if ($setting->qris_image)
                <x-public.card>
                    <h2 class="font-semibold text-masjid-900">Scan QRIS</h2>
                    <p class="mt-1 text-sm text-masjid-600">
                        Buka aplikasi mobile banking atau dompet digital Anda, lalu pindai kode berikut.
                    </p>
                    <x-public.image :src="Storage::url($setting->qris_image)" :alt="'Kode QRIS donasi '.$setting->name" class="mt-4 w-full max-w-xs rounded-xl border border-masjid-100" />
                </x-public.card>
            @endif

            <x-public.card>
                <h2 class="font-semibold text-masjid-900">Donasi tunai</h2>
                <p class="mt-1 text-sm text-masjid-600">
                    Kotak infaq tersedia di area masjid, {{ $setting->address }}. Untuk donasi dalam jumlah besar,
                    silakan hubungi bendahara DKM agar dapat dibuatkan tanda terima.
                </p>
                <a href="{{ route('kontak') }}" class="mt-3 inline-block text-sm font-medium text-masjid-600 hover:underline">
                    Hubungi pengurus &rarr;
                </a>
            </x-public.card>
        </div>

        <aside class="space-y-6">
            <x-public.card tone="dark">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-emas-300">Terhimpun bulan ini</h2>
                <p class="mt-2 text-2xl font-semibold">Rp {{ number_format($monthlyIncome, 0, ',', '.') }}</p>
                <p class="mt-1 text-sm text-masjid-100">Periode {{ today()->translatedFormat('F Y') }}</p>
                <a href="{{ route('laporan-keuangan') }}" class="mt-4 inline-block rounded-lg bg-white/15 px-4 py-2 text-sm font-medium hover:bg-white/25">
                    Lihat laporan lengkap
                </a>
            </x-public.card>

            <x-public.card>
                <h2 class="font-semibold text-masjid-900">Ke mana donasi disalurkan?</h2>
                <ul class="mt-3 space-y-2 text-sm text-masjid-700">
                    <li>Operasional harian masjid (listrik, air, kebersihan)</li>
                    <li>Honor pengisi kajian dan kegiatan dakwah</li>
                    <li>Pemeliharaan dan perbaikan sarana masjid</li>
                    <li>Santunan sosial untuk jamaah dan masyarakat sekitar</li>
                </ul>
                <p class="mt-3 text-xs text-masjid-500">
                    Rincian penggunaan dana dapat dilihat kapan saja pada halaman Laporan Keuangan.
                </p>
            </x-public.card>
        </aside>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('click', async (event) => {
            const tombol = event.target.closest('[data-copy-target]');

            if (!tombol) {
                return;
            }

            const sumber = document.getElementById(tombol.dataset.copyTarget);

            if (!sumber) {
                return;
            }

            try {
                await navigator.clipboard.writeText(sumber.textContent.trim());
                const teksAsli = tombol.textContent;
                tombol.textContent = 'Tersalin!';
                setTimeout(() => (tombol.textContent = teksAsli), 2000);
            } catch (error) {
                console.error(error);
            }
        });
    </script>
@endpush
