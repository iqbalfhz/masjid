@extends('layouts.public')

@section('title', 'Kotak Saran & Pengaduan')
@section('description', 'Sampaikan saran, keluhan, atau apresiasi Anda kepada pengurus Masjid An-Nur.')

@section('content')
    <x-public.page-header
        title="Kotak Saran & Pengaduan"
        subtitle="Masukan Anda membantu pengurus memperbaiki pelayanan masjid." />

    <div class="grid gap-8 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        <x-public.card>
            <form action="{{ route('saran.store') }}" method="post" class="relative space-y-5">
                @csrf
                <x-public.honeypot />
                <x-public.form-errors />

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-public.form-field name="name" label="Nama" help="Opsional — kosongkan bila ingin anonim." />
                    <x-public.form-field name="contact" label="Kontak (HP / email)"
                                         help="Opsional, tapi memudahkan pengurus mengabari tindak lanjut." />
                </div>

                <x-public.form-field name="category" label="Kategori" type="select" :options="$categories" required />

                <x-public.form-field name="message" label="Isi masukan" type="textarea" rows="6" required
                                     help="Ceritakan sedetail mungkin agar pengurus mudah menindaklanjuti." />

                <x-public.submit-button>Kirim masukan</x-public.submit-button>
            </form>
        </x-public.card>

        <aside class="space-y-6">
            <x-public.card>
                <h2 class="font-semibold text-masjid-900">Bagaimana masukan ditindaklanjuti?</h2>
                <ol class="mt-3 list-decimal space-y-2 ps-5 text-sm text-masjid-700">
                    <li>Masukan masuk ke pengurus dengan status <strong class="font-medium">Baru</strong>.</li>
                    <li>Sekretaris meninjau dan meneruskan ke bidang terkait.</li>
                    <li>Status berubah menjadi <strong class="font-medium">Diproses</strong>, lalu <strong class="font-medium">Selesai</strong>.</li>
                    <li>Bila Anda mengisi kontak, pengurus dapat mengabari hasilnya.</li>
                </ol>
            </x-public.card>

            <x-public.card>
                <h2 class="font-semibold text-masjid-900">Simpan nomor tiket</h2>
                <p class="mt-1 text-sm text-masjid-600">
                    Setelah mengirim, Anda menerima nomor tiket. Sebutkan nomor tersebut bila menanyakan
                    perkembangan masukan Anda kepada pengurus.
                </p>
            </x-public.card>

            <x-public.card tone="dark">
                <h2 class="font-semibold">Sudah cek FAQ?</h2>
                <p class="mt-1 text-sm text-masjid-100">
                    Banyak pertanyaan umum sudah dijawab di halaman FAQ.
                </p>
                <a href="{{ route('faq') }}" class="mt-3 inline-block rounded-lg bg-white/15 px-4 py-2 text-sm font-medium hover:bg-white/25">
                    Buka FAQ
                </a>
            </x-public.card>
        </aside>
    </div>
@endsection
