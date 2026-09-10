{{-- Proteksi spam sederhana (PRD bagian 6): field umpan yang disembunyikan dari
     manusia, plus penanda waktu render untuk mendeteksi submit instan oleh bot. --}}
@php
    $field = config('masjid.forms.honeypot_field');
    $timeField = config('masjid.forms.honeypot_time_field');
@endphp

<div aria-hidden="true" class="absolute left-[-9999px] h-0 w-0 overflow-hidden">
    <label for="{{ $field }}">Biarkan kolom ini kosong</label>
    <input id="{{ $field }}" type="text" name="{{ $field }}" value="" tabindex="-1" autocomplete="off">
</div>
<input type="hidden" name="{{ $timeField }}" value="{{ encrypt(now()->timestamp) }}">
