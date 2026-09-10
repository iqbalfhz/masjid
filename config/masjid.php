<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Jadwal Sholat
    |--------------------------------------------------------------------------
    |
    | Sumber jadwal sholat otomatis. Koordinat default mengarah ke lokasi
    | Masjid An-Nur (Lantai P3a, Tangcity Mall). Nilai ini bisa ditimpa lewat
    | Pengaturan Umum di admin panel; env hanya dipakai sebagai fallback.
    |
    */

    'prayer' => [
        'provider' => env('PRAYER_API_PROVIDER', 'aladhan'),
        'base_url' => env('PRAYER_API_BASE_URL', 'https://api.aladhan.com/v1'),
        'latitude' => (float) env('PRAYER_API_LATITUDE', -6.178306),
        'longitude' => (float) env('PRAYER_API_LONGITUDE', 106.631417),
        'timezone' => env('PRAYER_API_TIMEZONE', 'Asia/Jakarta'),

        /*
         * Metode hisab Aladhan. 20 = Kementerian Agama Republik Indonesia.
         */
        'method' => (int) env('PRAYER_API_METHOD', 20),

        /*
         * Mazhab penentuan waktu Ashar: 0 = Syafi'i, 1 = Hanafi.
         */
        'school' => (int) env('PRAYER_API_SCHOOL', 0),

        /*
         * Berapa bulan ke depan yang disinkronkan tiap kali scheduler berjalan.
         */
        'sync_months_ahead' => (int) env('PRAYER_API_SYNC_MONTHS', 2),
    ],

    /*
    |--------------------------------------------------------------------------
    | Web Push (Reminder Sholat)
    |--------------------------------------------------------------------------
    |
    | Generate pasangan kunci dengan: php artisan masjid:vapid-keys
    |
    */

    'push' => [
        'public_key' => env('VAPID_PUBLIC_KEY'),
        'private_key' => env('VAPID_PRIVATE_KEY'),
        'subject' => env('VAPID_SUBJECT', 'mailto:admin@masjidannur.test'),
        'default_minutes_before' => (int) env('PUSH_DEFAULT_MINUTES_BEFORE', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Proteksi Form Publik
    |--------------------------------------------------------------------------
    |
    | Nama field honeypot dan batas rate limit submit form jamaah
    | (testimoni, saran, RSVP, pendaftaran layanan).
    |
    */

    'forms' => [
        'honeypot_field' => env('HONEYPOT_FIELD', 'website_url'),
        'honeypot_time_field' => env('HONEYPOT_TIME_FIELD', 'form_rendered_at'),
        'honeypot_min_seconds' => (int) env('HONEYPOT_MIN_SECONDS', 3),
        'rate_limit' => env('PUBLIC_FORM_RATE_LIMIT', '5,1'),
    ],

];
