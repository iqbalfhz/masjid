@extends('layouts.public')

@section('title', 'Buku Tamu & Testimoni')
@section('description', 'Kesan dan pesan jamaah tentang Masjid An-Nur Tangcity Mall.')

@section('content')
    <x-public.page-header
        title="Buku Tamu & Testimoni"
        subtitle="Kesan dan pesan dari jamaah. Setiap pesan ditinjau pengurus sebelum ditampilkan." />

    <div class="grid gap-8 grid-cols-1 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        <div>
            @if ($testimonials->isEmpty())
                <x-public.empty-state message="Belum ada pesan yang tayang. Jadilah yang pertama menulis." />
            @else
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($testimonials as $testimonial)
                        <x-public.card as="blockquote" class="flex flex-col">
                            <p class="flex-1 text-masjid-800">&ldquo;{{ $testimonial->message }}&rdquo;</p>
                            <footer class="mt-4 border-t border-masjid-100 pt-3 text-sm">
                                <span class="font-medium text-masjid-800">{{ $testimonial->displayName() }}</span>
                                <span class="block text-xs text-masjid-500">{{ $testimonial->created_at->translatedFormat('d F Y') }}</span>
                            </footer>
                        </x-public.card>
                    @endforeach
                </div>

                <div class="mt-8">{{ $testimonials->links() }}</div>
            @endif
        </div>

        <aside>
            <x-public.card>
                <h2 class="font-semibold text-masjid-900">Tulis kesan & pesan</h2>
                <p class="mt-1 text-sm text-masjid-600">
                    Nama boleh dikosongkan bila ingin anonim. Pesan tayang setelah ditinjau pengurus.
                </p>

                <form action="{{ route('testimoni.store') }}" method="post" class="relative mt-4 space-y-4">
                    @csrf
                    <x-public.honeypot />
                    <x-public.form-errors />

                    <x-public.form-field name="name" label="Nama" help="Opsional — kosongkan untuk anonim." />
                    <x-public.form-field name="message" label="Pesan" type="textarea" rows="5" required
                                         help="Minimal 10 karakter." />

                    <x-public.submit-button class="w-full">Kirim pesan</x-public.submit-button>
                </form>
            </x-public.card>
        </aside>
    </div>
@endsection
