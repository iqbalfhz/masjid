{{-- Form konfirmasi kehadiran jamaah (PRD 5.1.3). --}}
@props(['subject', 'jenis', 'count' => 0])

@php
    $sisaKuota = $subject->rsvp_quota ? max(0, $subject->rsvp_quota - $count) : null;
@endphp

<x-public.card>
    <h2 class="font-semibold text-masjid-900">Konfirmasi kehadiran</h2>
    <p class="mt-1 text-sm text-masjid-600">
        Bantu panitia memperkirakan tempat dan konsumsi dengan mengisi form singkat ini.
    </p>

    @if ($sisaKuota !== null)
        <p class="mt-2 text-sm font-medium {{ $sisaKuota > 0 ? 'text-masjid-700' : 'text-red-600' }}">
            {{ $sisaKuota > 0 ? "Sisa kuota: {$sisaKuota} orang" : 'Kuota sudah penuh' }}
        </p>
    @endif

    @if ($sisaKuota === null || $sisaKuota > 0)
        <form action="{{ route('rsvp.store') }}" method="post" class="relative mt-4 space-y-4">
            @csrf
            <x-public.honeypot />
            <input type="hidden" name="jenis" value="{{ $jenis }}">
            <input type="hidden" name="id" value="{{ $subject->getKey() }}">

            <x-public.form-errors />

            <x-public.form-field name="name" label="Nama" required />
            <x-public.form-field name="phone" label="Nomor WhatsApp" type="tel" required />
            <x-public.form-field name="person_count" label="Jumlah orang" type="number" value="1" required />
            <x-public.form-field name="note" label="Catatan" type="textarea" rows="2"
                                 help="Opsional, misal membawa anak atau butuh kursi." />

            <x-public.submit-button class="w-full">Konfirmasi hadir</x-public.submit-button>
        </form>
    @endif
</x-public.card>
