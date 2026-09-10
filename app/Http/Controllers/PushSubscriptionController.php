<?php

namespace App\Http\Controllers;

use App\Models\PrayerSchedule;
use App\Models\PushSubscription;
use App\Services\PrayerScheduleService;
use App\Services\WebPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Langganan reminder sholat lewat Web Push (PRD 5.1.2).
 */
class PushSubscriptionController extends Controller
{
    public function publicKey(WebPushService $push): JsonResponse
    {
        return response()->json([
            'enabled' => $push->isConfigured(),
            'publicKey' => $push->publicKey(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'url', 'max:2000'],
            'keys.p256dh' => ['required', 'string', 'max:255'],
            'keys.auth' => ['required', 'string', 'max:255'],
            'prayers' => ['nullable', 'array'],
            'prayers.*' => ['string', 'in:'.implode(',', array_keys(PrayerSchedule::REMINDABLE))],
            'minutes_before' => ['nullable', 'integer', 'min:1', 'max:60'],
        ]);

        PushSubscription::query()->updateOrCreate(
            ['endpoint_hash' => hash('sha256', $validated['endpoint'])],
            [
                'endpoint' => $validated['endpoint'],
                'public_key' => $validated['keys']['p256dh'],
                'auth_token' => $validated['keys']['auth'],
                'enabled_prayers' => $validated['prayers'] ?? array_keys(PrayerSchedule::REMINDABLE),
                'minutes_before' => $validated['minutes_before'] ?? config('masjid.push.default_minutes_before'),
                'user_agent' => str($request->userAgent() ?? '')->limit(250)->value(),
            ],
        );

        return response()->json(['message' => 'Reminder sholat aktif. Terima kasih.']);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'url', 'max:2000'],
        ]);

        PushSubscription::query()
            ->where('endpoint_hash', hash('sha256', $validated['endpoint']))
            ->delete();

        return response()->json(['message' => 'Reminder sholat dinonaktifkan.']);
    }

    /**
     * Isi notifikasi yang diambil service worker saat menerima push.
     */
    public function content(PrayerScheduleService $prayers): JsonResponse
    {
        $next = $prayers->nextPrayer();

        if ($next === null) {
            return response()->json([
                'title' => 'Pengingat sholat',
                'body' => 'Jadwal sholat belum tersedia.',
            ]);
        }

        $minutes = (int) now()->diffInMinutes($next['time'], absolute: true);

        return response()->json([
            'title' => "Menjelang {$next['label']}",
            'body' => $minutes > 0
                ? "{$minutes} menit lagi memasuki waktu {$next['label']} ({$next['time']->format('H:i')} WIB)."
                : "Telah masuk waktu {$next['label']} ({$next['time']->format('H:i')} WIB).",
            'url' => route('jadwal-sholat'),
        ]);
    }
}
