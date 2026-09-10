@props(['src' => null, 'alt' => ''])

{{--
    Gambar dengan cadangan: bila berkasnya hilang atau gagal dimuat, otomatis
    diganti gambar placeholder supaya tata letak tidak rusak.
--}}
@php
    $fallback = asset('images/placeholder.svg');
@endphp

<img src="{{ $src ?: $fallback }}"
     alt="{{ $alt }}"
     loading="lazy"
     decoding="async"
     onerror="this.onerror=null;this.src='{{ $fallback }}';this.classList.add('object-contain','p-6','opacity-60')"
     {{ $attributes->merge(['class' => 'h-full w-full object-cover']) }}>
