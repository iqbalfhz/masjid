@extends('layouts.public')

@section('title', 'Kontak & Lokasi')
@section('description', 'Alamat, nomor kontak, dan peta lokasi Masjid An-Nur di Tangcity Mall.')

@section('content')
    <x-public.page-header
        title="Kontak & Lokasi"
        subtitle="Kami berada di dalam area Tangcity Mall dan terbuka untuk seluruh jamaah." />

    <div class="grid gap-8 grid-cols-1 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)]">
        <div class="space-y-6">
            <x-public.card>
                <h2 class="font-semibold text-masjid-900">Alamat</h2>
                <p class="mt-2 text-masjid-700">{{ $setting->address }}</p>

                <dl class="mt-4 space-y-3 text-sm">
                    @if ($setting->phone)
                        <div>
                            <dt class="text-masjid-500">Telepon / WhatsApp</dt>
                            <dd class="font-medium text-masjid-900">
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $setting->phone) }}" class="hover:underline">
                                    {{ $setting->phone }}
                                </a>
                            </dd>
                        </div>
                    @endif
                    @if ($setting->email)
                        <div>
                            <dt class="text-masjid-500">Email</dt>
                            <dd class="font-medium text-masjid-900">
                                <a href="mailto:{{ $setting->email }}" class="hover:underline">{{ $setting->email }}</a>
                            </dd>
                        </div>
                    @endif
                </dl>
            </x-public.card>

            @if ($mapsSrc = $setting->mapsEmbedSrc())
                <x-public.card class="p-0! overflow-hidden">
                    <iframe src="{{ $mapsSrc }}"
                            title="Peta lokasi {{ $setting->name }}"
                            class="h-80 w-full border-0"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            allowfullscreen></iframe>
                </x-public.card>
            @else
                <x-public.card>
                    <h2 class="font-semibold text-masjid-900">Peta lokasi</h2>
                    <p class="mt-2 text-sm text-masjid-600">
                        Peta belum dipasang pengurus. Sementara ini, Masjid An-Nur dapat ditemukan di
                        <strong>{{ $setting->address }}</strong>.
                    </p>
                </x-public.card>
            @endif

            @if ($contacts->isNotEmpty())
                <section>
                    <x-public.section-heading title="Pengurus yang dapat dihubungi" :href="route('profil')" linkLabel="Struktur lengkap" />

                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach ($contacts as $contact)
                            <x-public.card class="flex items-center gap-4">
                                <x-public.image :src="$contact->photo ? Storage::url($contact->photo) : null" class="h-14 w-14 shrink-0 rounded-full object-cover" />
                                <div>
                                    <p class="font-medium text-masjid-900">{{ $contact->name }}</p>
                                    <p class="text-sm text-masjid-600">{{ $contact->position }}</p>
                                </div>
                            </x-public.card>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        <aside class="space-y-6">
            <x-public.card>
                <h2 class="font-semibold text-masjid-900">Jam layanan</h2>
                <ul class="mt-3 space-y-2 text-sm text-masjid-700">
                    <li><strong class="font-medium">Masjid:</strong> buka sejak sebelum Subuh hingga setelah Isya.</li>
                    <li><strong class="font-medium">Sekretariat DKM:</strong> setiap hari kerja, jam operasional mall.</li>
                    <li><strong class="font-medium">Layanan peminjaman fasilitas:</strong> pengajuan diproses maksimal 3 hari kerja.</li>
                </ul>
            </x-public.card>

            <x-public.card tone="dark">
                <h2 class="font-semibold">Punya masukan?</h2>
                <p class="mt-1 text-sm text-masjid-100">
                    Sampaikan saran, keluhan, atau apresiasi Anda. Boleh anonim.
                </p>
                <a href="{{ route('saran.create') }}" class="mt-3 inline-block rounded-lg bg-white/15 px-4 py-2 text-sm font-medium hover:bg-white/25">
                    Buka kotak saran
                </a>
            </x-public.card>
        </aside>
    </div>
@endsection
