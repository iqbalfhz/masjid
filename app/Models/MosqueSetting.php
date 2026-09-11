<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Model;

/**
 * Pengaturan umum masjid — tabel singleton, selalu memakai baris pertama.
 */
#[Unguarded]
class MosqueSetting extends Model
{
    use RecordsActivity;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'prayer_reminder_settings' => 'array',
            'social_links' => 'array',
        ];
    }

    public static function current(): self
    {
        return once(fn (): self => static::query()->firstOrCreate([]));
    }

    /**
     * Apakah URL ini benar-benar boleh dimuat di dalam iframe?
     *
     * Google menolak sebagian besar alamat petanya disematkan, dan
     * penolakannya tidak bersuara — pengunjung hanya melihat kotak kosong.
     * Hanya dua bentuk yang diizinkan:
     *
     * 1. URL sematan resmi dari menu "Sematkan peta" (`/maps/embed?pb=...`)
     * 2. Alamat peta biasa yang diberi parameter `output=embed`
     *
     * Link berbagi (`maps.app.goo.gl`, `goo.gl/maps`) dan tautan hasil
     * pencarian biasa akan ditolak Google lewat header X-Frame-Options.
     */
    public static function isEmbeddableMapsUrl(?string $url): bool
    {
        if (blank($url)) {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST) ?: '';

        if (! str_ends_with($host, 'google.com')) {
            return false;
        }

        return str_contains($url, '/maps/embed')
            || str_contains($url, 'output=embed');
    }

    /**
     * Alamat peta yang siap dipasang di iframe.
     *
     * Koordinat masjid sudah tersimpan untuk perhitungan jadwal sholat, jadi
     * peta bisa selalu ditampilkan tanpa menuntut pengurus memahami perbedaan
     * "link berbagi" dan "URL sematan". URL sematan tetap dipakai bila diisi
     * dengan benar, karena hasilnya lebih rapi (penanda, zoom, label).
     */
    public function mapsEmbedSrc(): ?string
    {
        if (self::isEmbeddableMapsUrl($this->maps_embed_url)) {
            return $this->maps_embed_url;
        }

        if ($this->latitude === null || $this->longitude === null) {
            return null;
        }

        return 'https://maps.google.com/maps?'.http_build_query([
            'q' => $this->latitude.','.$this->longitude,
            'z' => 17,
            'hl' => 'id',
            'output' => 'embed',
        ]);
    }
}
