@extends('layouts.public')

@section('title', 'Layanan Zakat')
@section('description', 'Tunaikan zakat fitrah dan zakat maal Anda melalui panitia Masjid An-Nur Tangcity Mall.')

@section('content')
    <x-public.page-header
        title="Layanan Zakat"
        subtitle="Daftarkan zakat fitrah maupun zakat maal Anda. Pembayaran dilakukan manual ke panitia." />

    <div class="grid gap-8 lg:grid-cols-[2fr,1fr]">
        <x-public.card>
            <form action="{{ route('zakat.store') }}" method="post" class="relative space-y-5">
                @csrf
                <x-public.honeypot />
                <x-public.form-errors />

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-public.form-field name="name" label="Nama muzakki" required />
                    <x-public.form-field name="phone" label="Nomor WhatsApp" type="tel" required />
                </div>

                <x-public.form-field name="zakat_type" label="Jenis zakat" type="select"
                                     :options="$zakatTypes" value="fitrah" required
                                     help="Zakat fitrah dihitung per jiwa, zakat maal berdasarkan nominal harta." />

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-public.form-field name="soul_count" label="Jumlah jiwa" type="number"
                                         help="Diisi bila memilih zakat fitrah." />
                    <x-public.form-field name="amount" label="Nominal (Rp)" type="number"
                                         help="Diisi bila memilih zakat maal." />
                </div>

                <x-public.form-field name="notes" label="Catatan" type="textarea" rows="3"
                                     help="Opsional, misal nama anggota keluarga yang dizakati." />

                <x-public.submit-button>Daftarkan zakat</x-public.submit-button>
            </form>
        </x-public.card>

        <aside class="space-y-6">
            <x-public.card>
                <h2 class="font-semibold text-masjid-900">Alur penunaian</h2>
                <ol class="mt-3 list-decimal space-y-2 ps-5 text-sm text-masjid-700">
                    <li>Isi formulir pendaftaran zakat.</li>
                    <li>Simpan nomor pendaftaran yang muncul.</li>
                    <li>Tunaikan pembayaran secara tunai di masjid atau transfer ke rekening panitia.</li>
                    <li>Konfirmasi ke bendahara dengan menyebut nomor pendaftaran.</li>
                    <li>Status berubah menjadi <strong class="font-medium">Lunas</strong> setelah diverifikasi.</li>
                </ol>
            </x-public.card>

            <x-public.card>
                <h2 class="font-semibold text-masjid-900">Catatan penting</h2>
                <ul class="mt-3 space-y-2 text-sm text-masjid-700">
                    <li>Besaran zakat fitrah mengikuti ketetapan panitia tahun berjalan.</li>
                    <li>Zakat maal dihitung 2,5% dari harta yang telah mencapai nisab dan haul.</li>
                    <li>Penyaluran zakat dilaporkan terbuka di halaman Laporan Keuangan.</li>
                </ul>
                <a href="{{ route('laporan-keuangan') }}" class="mt-3 inline-block text-sm font-medium text-masjid-600 hover:underline">
                    Lihat laporan penyaluran &rarr;
                </a>
            </x-public.card>
        </aside>
    </div>
@endsection
