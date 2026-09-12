<?php

namespace App\Http\Controllers;

use App\Models\MosqueSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Web app manifest (PRD 5.1.2).
 *
 * Bukan sekadar pemanis "bisa dipasang di layar utama": di iPhone, Safari
 * hanya membuka Web Push untuk situs yang dipasang sebagai web app, dan itu
 * mensyaratkan manifest dengan `display` standalone. Tanpa berkas ini,
 * pengingat sholat mustahil diaktifkan dari iPhone.
 *
 * Disajikan lewat rute, bukan berkas statis, karena nama dan deskripsinya
 * mengikuti Pengaturan Umum tiap masjid — kodebase ini dipasang ulang per
 * masjid (PRD bagian 13).
 */
class WebManifestController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $setting = MosqueSetting::current();

        return response()->json([
            'name' => $setting->name,
            // Nama di bawah ikon layar utama dipotong sistem; dua kata pertama
            // biasanya sudah cukup mengenali masjidnya.
            'short_name' => Str::words($setting->name, 2, ''),
            'description' => $setting->description ?: $setting->tagline,
            'lang' => 'id',
            'dir' => 'ltr',
            'start_url' => '/',
            'scope' => '/',
            'display' => 'standalone',
            'background_color' => '#f0f7f4',
            'theme_color' => '#1d3b34',
            'icons' => $this->icons($setting),
        ], options: JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            ->header('Content-Type', 'application/manifest+json');
    }

    /**
     * Ikon bawaan selalu disertakan dengan ukuran yang pasti. Logo masjid —
     * yang ukurannya bebas — ditambahkan sebagai pilihan tambahan bila ada.
     *
     * @return list<array{src: string, sizes: string, type: string, purpose?: string}>
     */
    private function icons(MosqueSetting $setting): array
    {
        $icons = [
            ['src' => asset('images/icon-192.png'), 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any maskable'],
            ['src' => asset('images/icon-512.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
        ];

        if (filled($setting->logo)) {
            array_unshift($icons, [
                'src' => Storage::url($setting->logo),
                'sizes' => 'any',
                'type' => 'image/png',
            ]);
        }

        return $icons;
    }
}
