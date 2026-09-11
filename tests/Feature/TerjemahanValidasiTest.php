<?php

use Illuminate\Support\Arr;

beforeEach(function (): void {
    app()->setLocale('id');
});

/**
 * Tanpa lang/id, pesan validasi jatuh ke bahasa Inggris sementara nama
 * field-nya berbahasa Indonesia — jamaah melihat "The nama field is
 * required." di setiap form publik.
 */
it('menampilkan pesan validasi dalam bahasa Indonesia', function (string $aturan, mixed $nilai, string $harapan): void {
    $validator = validator(
        ['nama' => $nilai],
        ['nama' => $aturan],
        [],
        ['nama' => 'nama'],
    );

    expect($validator->errors()->first('nama'))->toBe($harapan);
})->with([
    'wajib diisi' => ['required', '', 'Nama wajib diisi.'],
    'email' => ['email', 'bukan-email', 'Nama harus berupa alamat email yang valid.'],
    'panjang maksimal' => ['string|max:5', 'terlalu panjang', 'Nama tidak boleh lebih dari 5 karakter.'],
    'panjang minimal' => ['string|min:5', 'abc', 'Nama minimal terdiri dari 5 karakter.'],
    'bilangan bulat' => ['integer', 'x', 'Nama harus berupa bilangan bulat.'],
]);

it('mengawali kalimat dengan huruf besar meski nama field ditulis kecil', function (): void {
    // Form publik menulis nama field huruf kecil lewat attributes(). Tanpa
    // :Attribute, pesannya jadi "nama wajib diisi." — janggal di awal kalimat.
    $pesan = validator(['nama' => ''], ['nama' => 'required'], [], ['nama' => 'nama'])
        ->errors()->first('nama');

    expect($pesan)->toStartWith('Nama');
});

it('menerjemahkan pesan yang muncul di halaman profil admin', function (): void {
    // Dua pesan yang terlihat setengah Inggris di halaman ganti kata sandi.
    expect(__('validation.current_password'))->toBe('Kata sandi salah.')
        ->and(validator(
            ['baru' => 'a', 'konfirmasi' => 'b'],
            ['baru' => 'same:konfirmasi'],
            [],
            ['baru' => 'kata sandi', 'konfirmasi' => 'konfirmasi kata sandi'],
        )->errors()->first('baru'))
        ->toBe('Kata sandi harus sama dengan konfirmasi kata sandi.');
});

it('tidak menyisakan satu pun kunci validasi yang belum diterjemahkan', function (): void {
    // Penjaga kelengkapan: saat Laravel menambah aturan baru di pembaruan
    // framework, test ini gagal — alih-alih aturan itu diam-diam kembali
    // berbahasa Inggris di hadapan jamaah.
    $inggris = require base_path('vendor/laravel/framework/src/Illuminate/Translation/lang/en/validation.php');
    $indonesia = require lang_path('id/validation.php');

    $kunciInggris = array_keys(Arr::dot(Arr::except($inggris, ['custom', 'attributes'])));
    $kunciIndonesia = array_keys(Arr::dot(Arr::except($indonesia, ['custom', 'attributes'])));

    $hilang = array_values(array_diff($kunciInggris, $kunciIndonesia));

    expect($hilang)->toBe([], 'Kunci belum diterjemahkan: '.implode(', ', $hilang));
});

it('tidak menyisakan teks bahasa Inggris dalam terjemahan', function (): void {
    $indonesia = Arr::dot(Arr::except(require lang_path('id/validation.php'), ['custom', 'attributes']));

    $masihInggris = array_keys(array_filter(
        $indonesia,
        fn (string $pesan): bool => str_contains($pesan, 'The ') || str_contains($pesan, ' field '),
    ));

    expect($masihInggris)->toBe([]);
});
