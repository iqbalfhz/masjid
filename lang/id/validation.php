<?php

/*
|--------------------------------------------------------------------------
| Pesan Validasi — Bahasa Indonesia
|--------------------------------------------------------------------------
|
| Laravel tidak menyertakan terjemahan Indonesia. Tanpa berkas ini, pesan
| validasi jatuh ke bahasa Inggris sementara nama field-nya berbahasa
| Indonesia, sehingga jamaah melihat kalimat campuran seperti "The nama
| field is required." di setiap form publik.
|
| Ditulis sendiri, bukan lewat paket seperti laravel-lang, agar tidak
| menambah dependency dan isinya bisa disesuaikan dengan gaya bahasa situs.
|
| `:Attribute` (huruf besar) dipakai di awal kalimat supaya nama field yang
| ditulis kecil — misalnya "nama" dari attributes() form publik — tampil
| sebagai "Nama wajib diisi.".
|
| tests/Feature/TerjemahanValidasiTest.php memastikan setiap kunci milik
| Laravel punya padanannya di sini, sehingga aturan baru dari pembaruan
| framework tidak diam-diam kembali berbahasa Inggris.
|
*/

return [

    'accepted' => ':Attribute harus disetujui.',
    'accepted_if' => ':Attribute harus disetujui bila :other bernilai :value.',
    'active_url' => ':Attribute harus berupa URL yang valid.',
    'after' => ':Attribute harus berupa tanggal setelah :date.',
    'after_or_equal' => ':Attribute harus berupa tanggal yang sama dengan atau setelah :date.',
    'alpha' => ':Attribute hanya boleh berisi huruf.',
    'alpha_dash' => ':Attribute hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
    'alpha_num' => ':Attribute hanya boleh berisi huruf dan angka.',
    'any_of' => ':Attribute tidak valid.',
    'array' => ':Attribute harus berupa larik.',
    'array_keys' => ':Attribute hanya boleh berisi kunci berikut: :values.',
    'ascii' => ':Attribute hanya boleh berisi karakter alfanumerik dan simbol satu bita.',
    'base64' => ':Attribute harus berupa string Base64 yang valid.',
    'before' => ':Attribute harus berupa tanggal sebelum :date.',
    'before_or_equal' => ':Attribute harus berupa tanggal yang sama dengan atau sebelum :date.',
    'between' => [
        'array' => ':Attribute harus memiliki :min sampai :max item.',
        'file' => ':Attribute harus berukuran :min sampai :max kilobyte.',
        'numeric' => ':Attribute harus bernilai antara :min dan :max.',
        'string' => ':Attribute harus terdiri dari :min sampai :max karakter.',
    ],
    'boolean' => ':Attribute harus bernilai benar atau salah.',
    'can' => ':Attribute berisi nilai yang tidak diizinkan.',
    'confirmed' => ':Attribute tidak cocok dengan konfirmasinya.',
    'contains' => ':Attribute kekurangan nilai yang wajib ada.',
    'current_password' => 'Kata sandi salah.',
    'date' => ':Attribute harus berupa tanggal yang valid.',
    'date_equals' => ':Attribute harus berupa tanggal yang sama dengan :date.',
    'date_format' => ':Attribute harus sesuai dengan format :format.',
    'decimal' => ':Attribute harus memiliki :decimal angka desimal.',
    'declined' => ':Attribute harus ditolak.',
    'declined_if' => ':Attribute harus ditolak bila :other bernilai :value.',
    'different' => ':Attribute dan :other harus berbeda.',
    'digits' => ':Attribute harus terdiri dari :digits digit.',
    'digits_between' => ':Attribute harus terdiri dari :min sampai :max digit.',
    'dimensions' => ':Attribute memiliki dimensi gambar yang tidak valid.',
    'distinct' => ':Attribute memiliki nilai ganda.',
    'doesnt_contain' => ':Attribute tidak boleh berisi salah satu dari: :values.',
    'doesnt_end_with' => ':Attribute tidak boleh diakhiri dengan salah satu dari: :values.',
    'doesnt_start_with' => ':Attribute tidak boleh diawali dengan salah satu dari: :values.',
    'email' => ':Attribute harus berupa alamat email yang valid.',
    'encoding' => ':Attribute harus dikodekan dalam :encoding.',
    'ends_with' => ':Attribute harus diakhiri dengan salah satu dari: :values.',
    'enum' => ':Attribute yang dipilih tidak valid.',
    'exists' => ':Attribute yang dipilih tidak valid.',
    'extensions' => ':Attribute harus memiliki salah satu ekstensi berikut: :values.',
    'file' => ':Attribute harus berupa berkas.',
    'filled' => ':Attribute harus memiliki nilai.',
    'gt' => [
        'array' => ':Attribute harus memiliki lebih dari :value item.',
        'file' => ':Attribute harus berukuran lebih dari :value kilobyte.',
        'numeric' => ':Attribute harus lebih besar dari :value.',
        'string' => ':Attribute harus lebih dari :value karakter.',
    ],
    'gte' => [
        'array' => ':Attribute harus memiliki :value item atau lebih.',
        'file' => ':Attribute harus berukuran :value kilobyte atau lebih.',
        'numeric' => ':Attribute harus lebih besar dari atau sama dengan :value.',
        'string' => ':Attribute harus terdiri dari :value karakter atau lebih.',
    ],
    'hex_color' => ':Attribute harus berupa warna heksadesimal yang valid.',
    'image' => ':Attribute harus berupa gambar.',
    'in' => ':Attribute yang dipilih tidak valid.',
    'in_array' => ':Attribute harus ada di dalam :other.',
    'in_array_keys' => ':Attribute harus berisi setidaknya salah satu kunci berikut: :values.',
    'integer' => ':Attribute harus berupa bilangan bulat.',
    'ip' => ':Attribute harus berupa alamat IP yang valid.',
    'ipv4' => ':Attribute harus berupa alamat IPv4 yang valid.',
    'ipv6' => ':Attribute harus berupa alamat IPv6 yang valid.',
    'json' => ':Attribute harus berupa string JSON yang valid.',
    'list' => ':Attribute harus berupa daftar.',
    'lowercase' => ':Attribute harus berupa huruf kecil.',
    'lt' => [
        'array' => ':Attribute harus memiliki kurang dari :value item.',
        'file' => ':Attribute harus berukuran kurang dari :value kilobyte.',
        'numeric' => ':Attribute harus kurang dari :value.',
        'string' => ':Attribute harus kurang dari :value karakter.',
    ],
    'lte' => [
        'array' => ':Attribute tidak boleh memiliki lebih dari :value item.',
        'file' => ':Attribute harus berukuran kurang dari atau sama dengan :value kilobyte.',
        'numeric' => ':Attribute harus kurang dari atau sama dengan :value.',
        'string' => ':Attribute harus terdiri dari :value karakter atau kurang.',
    ],
    'mac_address' => ':Attribute harus berupa alamat MAC yang valid.',
    'max' => [
        'array' => ':Attribute tidak boleh memiliki lebih dari :max item.',
        'file' => ':Attribute tidak boleh berukuran lebih dari :max kilobyte.',
        'numeric' => ':Attribute tidak boleh lebih dari :max.',
        'string' => ':Attribute tidak boleh lebih dari :max karakter.',
    ],
    'max_digits' => ':Attribute tidak boleh memiliki lebih dari :max digit.',
    'mimes' => ':Attribute harus berupa berkas bertipe: :values.',
    'mimetypes' => ':Attribute harus berupa berkas bertipe: :values.',
    'min' => [
        'array' => ':Attribute harus memiliki minimal :min item.',
        'file' => ':Attribute harus berukuran minimal :min kilobyte.',
        'numeric' => ':Attribute minimal bernilai :min.',
        'string' => ':Attribute minimal terdiri dari :min karakter.',
    ],
    'min_digits' => ':Attribute harus memiliki minimal :min digit.',
    'missing' => ':Attribute tidak boleh ada.',
    'missing_if' => ':Attribute tidak boleh ada bila :other bernilai :value.',
    'missing_unless' => ':Attribute tidak boleh ada kecuali :other bernilai :value.',
    'missing_with' => ':Attribute tidak boleh ada bila :values diisi.',
    'missing_with_all' => ':Attribute tidak boleh ada bila :values semuanya diisi.',
    'multiple_of' => ':Attribute harus merupakan kelipatan dari :value.',
    'not_in' => ':Attribute yang dipilih tidak valid.',
    'not_regex' => 'Format :attribute tidak valid.',
    'numeric' => ':Attribute harus berupa angka.',
    'password' => [
        'letters' => ':Attribute harus berisi setidaknya satu huruf.',
        'mixed' => ':Attribute harus berisi setidaknya satu huruf besar dan satu huruf kecil.',
        'numbers' => ':Attribute harus berisi setidaknya satu angka.',
        'symbols' => ':Attribute harus berisi setidaknya satu simbol.',
        'uncompromised' => ':Attribute ini pernah muncul dalam kebocoran data. Silakan pilih :attribute yang lain.',
    ],
    'present' => ':Attribute wajib ada.',
    'present_if' => ':Attribute wajib ada bila :other bernilai :value.',
    'present_unless' => ':Attribute wajib ada kecuali :other bernilai :value.',
    'present_with' => ':Attribute wajib ada bila :values diisi.',
    'present_with_all' => ':Attribute wajib ada bila :values semuanya diisi.',
    'prohibited' => ':Attribute tidak diizinkan.',
    'prohibited_if' => ':Attribute tidak diizinkan bila :other bernilai :value.',
    'prohibited_if_accepted' => ':Attribute tidak diizinkan bila :other disetujui.',
    'prohibited_if_declined' => ':Attribute tidak diizinkan bila :other ditolak.',
    'prohibited_unless' => ':Attribute tidak diizinkan kecuali :other termasuk dalam :values.',
    'prohibits' => ':Attribute melarang :other untuk diisi.',
    'regex' => 'Format :attribute tidak valid.',
    'required' => ':Attribute wajib diisi.',
    'required_array_keys' => ':Attribute harus berisi entri untuk: :values.',
    'required_if' => ':Attribute wajib diisi bila :other bernilai :value.',
    'required_if_accepted' => ':Attribute wajib diisi bila :other disetujui.',
    'required_if_declined' => ':Attribute wajib diisi bila :other ditolak.',
    'required_unless' => ':Attribute wajib diisi kecuali :other termasuk dalam :values.',
    'required_with' => ':Attribute wajib diisi bila :values diisi.',
    'required_with_all' => ':Attribute wajib diisi bila :values semuanya diisi.',
    'required_without' => ':Attribute wajib diisi bila :values tidak diisi.',
    'required_without_all' => ':Attribute wajib diisi bila tidak satu pun dari :values diisi.',
    'same' => ':Attribute harus sama dengan :other.',
    'size' => [
        'array' => ':Attribute harus berisi :size item.',
        'file' => ':Attribute harus berukuran :size kilobyte.',
        'numeric' => ':Attribute harus bernilai :size.',
        'string' => ':Attribute harus terdiri dari :size karakter.',
    ],
    'starts_with' => ':Attribute harus diawali dengan salah satu dari: :values.',
    'string' => ':Attribute harus berupa teks.',
    'timezone' => ':Attribute harus berupa zona waktu yang valid.',
    'unique' => ':Attribute sudah digunakan.',
    'uploaded' => ':Attribute gagal diunggah.',
    'uppercase' => ':Attribute harus berupa huruf besar.',
    'url' => ':Attribute harus berupa URL yang valid.',
    'ulid' => ':Attribute harus berupa ULID yang valid.',
    'uuid' => ':Attribute harus berupa UUID yang valid.',

    /*
    | Pesan khusus per-field dengan konvensi "field.aturan". Form publik
    | menulis nama field-nya sendiri lewat attributes(), jadi di sini
    | dibiarkan kosong.
    */
    'custom' => [],

    'attributes' => [],

];
