<?php

namespace App\Http\Controllers;

use App\Models\MosqueSetting;
use App\Models\PrayerSchedule;
use App\Services\PrayerScheduleService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Jadwal sholat bulanan (PRD 5.1.2) beserta opsi berlangganan reminder.
 */
class PrayerScheduleController extends Controller
{
    public function __invoke(Request $request, PrayerScheduleService $prayers): View
    {
        $month = Carbon::createFromFormat('Y-m', $request->string('bulan')->toString() ?: today()->format('Y-m'))
            ?: today();

        $schedules = PrayerSchedule::query()
            ->forMonth($month->year, $month->month)
            ->orderBy('date')
            ->get();

        $reminder = MosqueSetting::current()->prayer_reminder_settings ?? [];

        return view('public.jadwal-sholat', [
            'month' => $month,
            'schedules' => $schedules,
            'todaySchedule' => $prayers->today(),
            'nextPrayer' => $prayers->nextPrayer(),
            'reminderEnabled' => (bool) ($reminder['enabled'] ?? false),
            'reminderPrayers' => $reminder['prayers'] ?? array_keys(PrayerSchedule::REMINDABLE),
        ]);
    }
}
