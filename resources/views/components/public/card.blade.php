@props(['as' => 'div', 'tone' => 'light'])

@php
    /*
     * Nada kartu ditentukan lewat prop, bukan dengan menimpa kelas dari luar.
     * Tailwind menyusun utility berdasarkan urutan di stylesheet, bukan urutan
     * penulisan di atribut class — jadi `class="bg-masjid-800"` dari pemanggil
     * justru kalah oleh `bg-white` bawaan komponen dan teks terang menjadi
     * tidak terbaca di atas latar putih.
     */
    $tones = [
        'light' => 'border-masjid-100 bg-white text-masjid-900 shadow-sm',
        'dark' => 'border-masjid-700/40 bg-masjid-800 text-white shadow-sm',
        'accent' => 'border-transparent bg-mesh-masjid text-white shadow-sm',
        'danger' => 'border-red-200 bg-red-50 text-red-900',
    ];

    $base = 'rounded-2xl border p-5 '.($tones[$tone] ?? $tones['light']);
@endphp

<{{ $as }} {{ $attributes->merge(['class' => $base]) }}>
    {{ $slot }}
</{{ $as }}>
