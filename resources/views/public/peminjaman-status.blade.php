@extends('layouts.public')

@section('title', 'Cek Status Peminjaman')
@section('description', 'Lacak status pengajuan peminjaman fasilitas Masjid An-Nur dengan nomor pengajuan Anda.')

@section('content')
    <x-public.page-header
        title="Cek Status Peminjaman"
        subtitle="Masukkan nomor pengajuan yang Anda terima saat mengirim formulir." />

    <div class="grid gap-8 lg:grid-cols-[2fr,1fr]">
        <div class="space-y-6">
            <x-public.card>
                <form method="get" class="flex flex-wrap items-end gap-3">
                    <div class="min-w-56 flex-1">
                        <label for="nomor" class="block text-sm font-medium text-masjid-800">Nomor pengajuan</label>
                        <input id="nomor" type="text" name="nomor" value="{{ $number }}" placeholder="Contoh: PJF-20260910-A1B2"
                               class="mt-1 w-full rounded-lg border border-masjid-200 px-3 py-2 font-mono text-sm shadow-sm focus:ring-2 focus:ring-masjid-400">
                    </div>
                    <button type="submit" class="rounded-lg bg-masjid-600 px-5 py-2 text-sm font-semibold text-white hover:bg-masjid-700">Cek status</button>
                </form>
            </x-public.card>

            @if ($number !== '' && ! $booking)
                <x-public.card class="border-red-200 bg-red-50">
                    <p class="font-medium text-red-800">Nomor pengajuan tidak ditemukan.</p>
                    <p class="mt-1 text-sm text-red-700">
                        Periksa kembali penulisannya, atau hubungi sekretariat DKM bila Anda kehilangan nomor pengajuan.
                    </p>
                </x-public.card>
            @endif

            @if ($booking)
                <x-public.card>
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-masjid-500">Nomor pengajuan</p>
                            <p class="font-mono text-lg font-semibold text-masjid-900">{{ $booking->booking_number }}</p>
                        </div>
                        <x-public.badge :color="$booking->status === \App\Enums\BookingStatus::Disetujui ? 'masjid' : 'emas'">
                            {{ $booking->status->getLabel() }}
                        </x-public.badge>
                    </div>

                    <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm text-masjid-500">Fasilitas</dt>
                            <dd class="font-medium text-masjid-900">{{ $booking->facility->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-masjid-500">Keperluan</dt>
                            <dd class="font-medium text-masjid-900">{{ $booking->purpose }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-masjid-500">Tanggal</dt>
                            <dd class="font-medium text-masjid-900">{{ $booking->booking_date->translatedFormat('l, d F Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-masjid-500">Waktu</dt>
                            <dd class="font-medium text-masjid-900">
                                {{ substr($booking->start_time, 0, 5) }}–{{ substr($booking->end_time, 0, 5) }} WIB
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-masjid-500">Pemohon</dt>
                            <dd class="font-medium text-masjid-900">{{ $booking->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-masjid-500">Diajukan</dt>
                            <dd class="font-medium text-masjid-900">{{ $booking->created_at->translatedFormat('d F Y, H:i') }} WIB</dd>
                        </div>
                    </dl>

                    @if ($booking->approval_note)
                        <div class="mt-6 rounded-xl bg-masjid-50 p-4">
                            <p class="text-sm font-medium text-masjid-800">Catatan pengurus</p>
                            <p class="mt-1 text-sm text-masjid-700">{{ $booking->approval_note }}</p>
                        </div>
                    @endif

                    @if ($booking->reviewed_at)
                        <p class="mt-4 text-xs text-masjid-500">
                            Ditinjau pada {{ $booking->reviewed_at->translatedFormat('d F Y, H:i') }} WIB.
                        </p>
                    @else
                        <p class="mt-4 text-xs text-masjid-500">
                            Pengajuan Anda masih dalam antrean peninjauan pengurus.
                        </p>
                    @endif
                </x-public.card>
            @endif
        </div>

        <aside>
            <x-public.card>
                <h2 class="font-semibold text-masjid-900">Arti setiap status</h2>
                <ul class="mt-3 space-y-3 text-sm">
                    <li>
                        <span class="font-medium text-masjid-800">Menunggu Persetujuan</span>
                        <p class="text-masjid-600">Pengajuan sudah masuk dan sedang ditinjau pengurus.</p>
                    </li>
                    <li>
                        <span class="font-medium text-masjid-800">Disetujui</span>
                        <p class="text-masjid-600">Slot waktu sudah dikunci untuk Anda.</p>
                    </li>
                    <li>
                        <span class="font-medium text-masjid-800">Ditolak</span>
                        <p class="text-masjid-600">Pengajuan tidak dapat dipenuhi; alasan tercantum pada catatan pengurus.</p>
                    </li>
                </ul>

                <a href="{{ route('fasilitas.create') }}" class="mt-4 inline-block text-sm font-medium text-masjid-600 hover:underline">
                    Ajukan peminjaman baru &rarr;
                </a>
            </x-public.card>
        </aside>
    </div>
@endsection
