<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Membuat pasangan kunci VAPID untuk Web Push (langkah 8 Tutorial Instalasi).
 */
class GenerateVapidKeys extends Command
{
    protected $signature = 'masjid:vapid-keys';

    protected $description = 'Buat pasangan kunci VAPID untuk reminder push notification';

    public function handle(): int
    {
        $options = [
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'prime256v1',
            'config' => $this->opensslConfigPath(),
        ];

        $key = openssl_pkey_new($options);

        if ($key === false) {
            $this->components->error('Gagal membuat kunci VAPID.');

            while ($error = openssl_error_string()) {
                $this->line("  {$error}");
            }

            $this->components->warn('Pastikan ekstensi OpenSSL aktif dan mendukung kurva prime256v1.');

            return self::FAILURE;
        }

        $details = openssl_pkey_get_details($key);
        openssl_pkey_export($key, $pem, null, $options);

        // Kunci publik dikirim ke browser dalam bentuk titik tak terkompresi.
        $publicKey = $this->base64UrlEncode("\x04".$details['ec']['x'].$details['ec']['y']);

        // Kunci privat cukup disimpan sebagai PEM ter-base64 agar mudah masuk .env.
        $privateKey = base64_encode((string) $pem);

        $this->components->info('Kunci VAPID berhasil dibuat. Salin dua baris ini ke berkas .env:');
        $this->newLine();
        $this->line("VAPID_PUBLIC_KEY={$publicKey}");
        $this->line("VAPID_PRIVATE_KEY={$privateKey}");
        $this->newLine();
        $this->components->warn('Simpan kunci privat dengan aman dan jangan pernah di-commit ke repository.');

        return self::SUCCESS;
    }

    /**
     * Sebagian instalasi PHP di Windows tidak menemukan openssl.cnf bawaan,
     * sehingga pembuatan kunci gagal. Sediakan konfigurasi minimal sendiri
     * bila berkas bawaannya tidak tersedia.
     */
    private function opensslConfigPath(): string
    {
        $configured = getenv('OPENSSL_CONF');

        if (is_string($configured) && is_file($configured)) {
            return $configured;
        }

        $path = storage_path('app/openssl-vapid.cnf');

        if (! is_file($path)) {
            @mkdir(dirname($path), 0755, true);
            file_put_contents($path, "[req]\ndistinguished_name = req_distinguished_name\n[req_distinguished_name]\n");
        }

        return $path;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
