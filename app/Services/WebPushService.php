<?php

namespace App\Services;

use App\Models\PushSubscription;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Pengiriman Web Push untuk reminder sholat (PRD 5.1.2 & Fase 4).
 *
 * Catatan implementasi: paket `minishlink/web-push` belum mendukung Guzzle 8
 * yang dipakai Laravel 13, jadi protokolnya diimplementasikan langsung di sini.
 * Yang dikirim adalah push tanpa payload ("tickle") — service worker yang
 * menerimanya lalu mengambil isi notifikasi dari server. Pendekatan ini sah
 * menurut spesifikasi Web Push, tidak memerlukan enkripsi payload, dan tetap
 * aman karena tidak ada data yang melewati push service.
 */
class WebPushService
{
    /**
     * Umur token VAPID; spesifikasi membatasi maksimal 24 jam.
     */
    private const TOKEN_TTL_SECONDS = 12 * 3600;

    public function isConfigured(): bool
    {
        return filled(config('masjid.push.public_key')) && filled(config('masjid.push.private_key'));
    }

    public function publicKey(): ?string
    {
        return config('masjid.push.public_key');
    }

    /**
     * Kirim satu notifikasi. Langganan yang sudah tidak berlaku (404/410)
     * dihapus supaya tabel tidak menumpuk endpoint mati.
     */
    public function send(PushSubscription $subscription, int $ttlSeconds = 900): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        $endpoint = $subscription->endpoint;
        $audience = $this->audienceFor($endpoint);

        $response = Http::withHeaders([
            'Authorization' => 'vapid t='.$this->createToken($audience).', k='.$this->publicKey(),
            'TTL' => (string) $ttlSeconds,
            'Urgency' => 'high',
            'Content-Length' => '0',
        ])->timeout(10)->send('POST', $endpoint);

        if ($response->status() === 404 || $response->status() === 410) {
            $subscription->delete();

            return false;
        }

        if ($response->failed()) {
            Log::warning('Web push gagal dikirim', [
                'endpoint_hash' => $subscription->endpoint_hash,
                'status' => $response->status(),
            ]);

            return false;
        }

        return true;
    }

    /**
     * Origin push service, dipakai sebagai klaim `aud` pada token VAPID.
     */
    private function audienceFor(string $endpoint): string
    {
        $parts = parse_url($endpoint);

        if (! isset($parts['scheme'], $parts['host'])) {
            throw new RuntimeException('Endpoint langganan push tidak valid.');
        }

        return $parts['scheme'].'://'.$parts['host'];
    }

    /**
     * JWT ES256 yang ditandatangani kunci privat VAPID.
     */
    private function createToken(string $audience): string
    {
        $header = $this->base64UrlEncode((string) json_encode(['typ' => 'JWT', 'alg' => 'ES256']));

        $payload = $this->base64UrlEncode((string) json_encode([
            'aud' => $audience,
            'exp' => now()->addSeconds(self::TOKEN_TTL_SECONDS)->timestamp,
            'sub' => config('masjid.push.subject'),
        ]));

        $signature = $this->sign("{$header}.{$payload}");

        return "{$header}.{$payload}.{$signature}";
    }

    private function sign(string $data): string
    {
        $pem = base64_decode((string) config('masjid.push.private_key'), true);
        $key = $pem === false ? false : openssl_pkey_get_private($pem);

        if ($key === false) {
            throw new RuntimeException('VAPID_PRIVATE_KEY tidak valid. Jalankan: php artisan masjid:vapid-keys');
        }

        if (! openssl_sign($data, $derSignature, $key, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Gagal menandatangani token VAPID.');
        }

        return $this->base64UrlEncode($this->derToRaw($derSignature));
    }

    /**
     * OpenSSL menghasilkan tanda tangan ECDSA berformat DER, sedangkan JWS
     * membutuhkan gabungan mentah r||s sepanjang 64 byte.
     */
    private function derToRaw(string $der): string
    {
        $offset = 0;

        if (ord($der[$offset++]) !== 0x30) {
            throw new RuntimeException('Tanda tangan ECDSA tidak berformat DER.');
        }

        // Lewati panjang sequence (bisa satu byte atau bentuk panjang).
        $lengthByte = ord($der[$offset++]);
        if ($lengthByte > 0x80) {
            $offset += $lengthByte - 0x80;
        }

        $r = $this->readDerInteger($der, $offset);
        $s = $this->readDerInteger($der, $offset);

        return str_pad($r, 32, "\x00", STR_PAD_LEFT).str_pad($s, 32, "\x00", STR_PAD_LEFT);
    }

    private function readDerInteger(string $der, int &$offset): string
    {
        if (ord($der[$offset++]) !== 0x02) {
            throw new RuntimeException('Tanda tangan ECDSA tidak berformat DER.');
        }

        $length = ord($der[$offset++]);
        $value = substr($der, $offset, $length);
        $offset += $length;

        return ltrim($value, "\x00");
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
