@extends('layouts.public')

@section('title', 'Layanan Kurban & Aqiqah')
@section('description', 'Daftarkan hewan kurban atau aqiqah Anda melalui panitia Masjid An-Nur Tangcity Mall.')

@section('content')
    <x-public.page-header
        title="Layanan Kurban & Aqiqah"
        subtitle="Isi formulir berikut untuk mendaftar. Pembayaran dilakukan manual lewat transfer ke rekening panitia." />

    <div class="grid gap-8 lg:grid-cols-[2fr,1fr]">
        <x-public.card>
            <form action="{{ route('kurban.store') }}" method="post" class="relative space-y-5">
                @csrf
                <x-public.honeypot />
                <x-public.form-errors />

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-public.form-field name="name" label="Nama pendaftar" required />
                    <x-public.form-field name="phone" label="Nomor WhatsApp" type="tel" required />
                </div>

                <div class="grid gap-5 sm:grid-cols-3">
                    <x-public.form-field name="service_type" label="Jenis layanan" type="select"
                                         :options="$serviceTypes" value="kurban" required />
                    <x-public.form-field name="animal_type" label="Jenis hewan" type="select"
                                         :options="$animalTypes" required />
                    <x-public.form-field name="quantity" label="Jumlah" type="number" value="1" required />
                </div>

                <x-public.form-field name="notes" label="Catatan" type="textarea" rows="3"
                                     help="Opsional, misal nama yang diatasnamakan atau permintaan khusus." />

                <x-public.submit-button>Daftarkan sekarang</x-public.submit-button>
            </form>
        </x-public.card>

        <aside class="space-y-6">
            <x-public.card>
                <h2 class="font-semibold text-masjid-900">Alur pendaftaran</h2>
                <ol class="mt-3 list-decimal space-y-2 ps-5 text-sm text-masjid-700">
                    <li>Isi dan kirim formulir di samping.</li>
                    <li>Simpan nomor pendaftaran yang muncul sebagai bukti.</li>
                    <li>Transfer ke rekening panitia sesuai nominal yang disepakati.</li>
                    <li>Konfirmasi ke bendahara dengan menyebut nomor pendaftaran.</li>
                    <li>Bendahara menandai status Anda menjadi <strong class="font-medium">Lunas</strong>.</li>
                </ol>
            </x-public.card>

            @if ($setting->bank_account_number)
                <x-public.card>
                    <h2 class="font-semibold text-masjid-900">Rekening panitia</h2>
                    <dl class="mt-3 space-y-2 text-sm">
                        <div>
                            <dt class="text-masjid-500">Bank</dt>
                            <dd class="font-medium text-masjid-900">{{ $setting->bank_name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-masjid-500">Nomor rekening</dt>
                            <dd class="font-mono font-semibold text-masjid-900">{{ $setting->bank_account_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-masjid-500">Atas nama</dt>
                            <dd class="font-medium text-masjid-900">{{ $setting->bank_account_name ?? '—' }}</dd>
                        </div>
                    </dl>
                </x-public.card>
            @endif

            <x-public.card class="bg-masjid-800 text-white">
                <h2 class="font-semibold">Butuh penjelasan lebih dulu?</h2>
                <p class="mt-1 text-sm text-masjid-100">
                    Hubungi panitia kurban di sekretariat DKM, {{ $setting->address }}.
                </p>
                <a href="{{ route('kontak') }}" class="mt-3 inline-block rounded-lg bg-white/15 px-4 py-2 text-sm font-medium hover:bg-white/25">
                    Lihat kontak
                </a>
            </x-public.card>
        </aside>
    </div>
@endsection
