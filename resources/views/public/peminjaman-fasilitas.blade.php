@extends('layouts.public')

@section('title', 'Peminjaman Fasilitas')
@section('description', 'Ajukan peminjaman ruang dan fasilitas Masjid An-Nur untuk akad nikah, rapat, atau kegiatan lain.')

@section('content')
    <x-public.page-header
        title="Peminjaman Fasilitas"
        subtitle="Ajukan pemakaian ruang masjid. Cek dulu kalender ketersediaan agar tidak bentrok dengan agenda lain." />

    <div class="grid gap-8 grid-cols-1 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        <x-public.card>
            <form action="{{ route('fasilitas.store') }}" method="post" class="relative space-y-5">
                @csrf
                <x-public.honeypot />
                <x-public.form-errors />

                <x-public.form-field
                    name="facility_id"
                    label="Fasilitas yang dipinjam"
                    type="select"
                    :options="$facilities->pluck('name', 'id')->all()"
                    required />

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-public.form-field name="name" label="Nama pemohon" required />
                    <x-public.form-field name="phone" label="Nomor WhatsApp" type="tel" required />
                </div>

                <x-public.form-field name="purpose" label="Keperluan" required
                                     help="Contoh: akad nikah, rapat pengurus RT, pelatihan remaja masjid." />

                <div class="grid gap-5 sm:grid-cols-3">
                    <x-public.form-field name="booking_date" label="Tanggal pemakaian" type="date" required />
                    <x-public.form-field name="start_time" label="Jam mulai" type="time" required />
                    <x-public.form-field name="end_time" label="Jam selesai" type="time" required />
                </div>

                <x-public.submit-button>Ajukan peminjaman</x-public.submit-button>
            </form>
        </x-public.card>

        <aside class="space-y-6">
            <x-public.card>
                <h2 class="font-semibold text-masjid-900">Kalender ketersediaan</h2>
                <p class="mt-1 text-sm text-masjid-600">
                    Jadwal berikut sudah terpakai atau sedang diproses. Hindari memilih waktu yang bertabrakan.
                </p>

                @if ($upcomingBookings->isEmpty())
                    <p class="mt-4 rounded-xl border border-dashed border-masjid-200 p-4 text-center text-sm text-masjid-500">
                        Belum ada jadwal terpakai. Semua tanggal masih tersedia.
                    </p>
                @else
                    <ul class="mt-4 space-y-3 text-sm">
                        @foreach ($upcomingBookings as $booking)
                            <li class="border-b border-masjid-100 pb-3 last:border-0 last:pb-0">
                                <p class="font-medium text-masjid-800">{{ $booking->facility->name }}</p>
                                <p class="text-masjid-600">
                                    {{ $booking->booking_date->translatedFormat('l, d F Y') }}
                                    &middot;
                                    {{ substr($booking->start_time, 0, 5) }}–{{ substr($booking->end_time, 0, 5) }}
                                </p>
                                <x-public.badge :color="$booking->status === \App\Enums\BookingStatus::Disetujui ? 'masjid' : 'gray'" class="mt-1">
                                    {{ $booking->status->getLabel() }}
                                </x-public.badge>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-public.card>

            <x-public.card>
                <h2 class="font-semibold text-masjid-900">Fasilitas tersedia</h2>
                <ul class="mt-3 space-y-3 text-sm">
                    @foreach ($facilities as $facility)
                        <li class="border-b border-masjid-100 pb-3 last:border-0 last:pb-0">
                            <p class="font-medium text-masjid-800">{{ $facility->name }}</p>
                            @if ($facility->capacity)
                                <p class="text-xs text-masjid-500">Kapasitas ± {{ $facility->capacity }} orang</p>
                            @endif
                            @if ($facility->description)
                                <p class="mt-1 text-masjid-600">{{ $facility->description }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </x-public.card>

            <x-public.card tone="dark">
                <h2 class="font-semibold">Sudah pernah mengajukan?</h2>
                <p class="mt-1 text-sm text-masjid-100">
                    Cek status pengajuan Anda dengan nomor pengajuan yang diterima sebelumnya.
                </p>
                <a href="{{ route('fasilitas.status') }}" class="mt-3 inline-block rounded-lg bg-white/15 px-4 py-2 text-sm font-medium hover:bg-white/25">
                    Cek status pengajuan
                </a>
            </x-public.card>
        </aside>
    </div>
@endsection
