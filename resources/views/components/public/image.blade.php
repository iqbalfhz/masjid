@props(['src' => null, 'alt' => ''])

@php
    /*
     * Gambar dengan cadangan: berkas yang hilang otomatis diganti placeholder
     * supaya tata letak tidak rusak.
     *
     * Penggantiannya dikerjakan resources/js/app.js lewat data-fallback, bukan
     * atribut onerror. CSP halaman publik memblokir event handler inline, dan
     * gambar yang gagal dimuat akan dibiarkan kosong tanpa pesan apa pun.
     *
     * Kelas dari pemanggil MENGGANTI bawaan, bukan digabung. Tailwind memilih
     * pemenang berdasarkan urutan di stylesheet, bukan urutan penulisan di
     * atribut — menggabungkan `h-full w-full` bawaan dengan `h-24 w-24` dari
     * pemanggil membuat foto pengurus melar memenuhi kartu.
     */
    $fallback = asset('images/placeholder.svg');
    $classes = $attributes->get('class') ?: 'h-full w-full object-cover';
@endphp

<img
    {{ $attributes->except('class') }}
    src="{{ $src ?: $fallback }}"
    alt="{{ $alt }}"
    loading="lazy"
    decoding="async"
    class="{{ $classes }}"
    data-fallback="{{ $fallback }}"
>
