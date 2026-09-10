<?php

use Illuminate\Support\Facades\Blade;

/**
 * Tailwind menentukan utility mana yang menang dari urutannya di stylesheet,
 * bukan dari urutan penulisan di atribut class. Karena itu komponen tidak boleh
 * menggabungkan kelas bawaan yang sejenis dengan kelas dari pemanggil — kelas
 * pemanggil harus benar-benar menggantikannya.
 */
it('memakai ukuran dari pemanggil pada komponen gambar, bukan ukuran bawaan', function (): void {
    $html = Blade::render('<x-public.image src="/foto.jpg" class="h-24 w-24 rounded-full object-cover" />');

    expect($html)
        ->toContain('h-24')
        ->toContain('w-24')
        ->not->toContain('h-full')
        ->not->toContain('w-full');
});

it('memakai ukuran bawaan komponen gambar ketika pemanggil tidak menentukan', function (): void {
    $html = Blade::render('<x-public.image src="/foto.jpg" />');

    expect($html)->toContain('h-full w-full object-cover');
});

it('jatuh ke placeholder ketika sumber gambar kosong', function (): void {
    $html = Blade::render('<x-public.image :src="null" />');

    expect($html)->toContain('images/placeholder.svg');
});

it('memakai ukuran dari pemanggil pada komponen ikon', function (): void {
    $html = Blade::render('<x-public.icon name="clock" class="h-4 w-4 text-masjid-400" />');

    expect($html)
        ->toContain('h-4')
        ->toContain('w-4')
        ->not->toContain('h-5')
        ->not->toContain('w-5');
});

it('memakai nada gelap tanpa beradu dengan latar putih bawaan kartu', function (): void {
    $html = Blade::render('<x-public.card tone="dark">Isi</x-public.card>');

    expect($html)
        ->toContain('bg-masjid-800')
        ->toContain('text-white')
        ->not->toContain('bg-white');
});

it('memakai latar putih pada kartu terang', function (): void {
    $html = Blade::render('<x-public.card>Isi</x-public.card>');

    expect($html)
        ->toContain('bg-white')
        ->not->toContain('bg-masjid-800');
});

it('menandai kartu peringatan dengan nada danger', function (): void {
    $html = Blade::render('<x-public.card tone="danger">Tidak ditemukan</x-public.card>');

    expect($html)
        ->toContain('bg-red-50')
        ->not->toContain('bg-white');
});
