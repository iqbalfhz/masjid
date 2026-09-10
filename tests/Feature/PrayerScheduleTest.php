<?php

use App\Models\PrayerSchedule;
use App\Models\PushSubscription;
use App\Services\PrayerScheduleService;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    seedMasterData();
});

/**
 * Respons Aladhan tiruan untuk dua hari pertama sebuah bulan.
 */
function aladhanResponse(string $month, string $year): array
{
    return [
        'data' => collect([1, 2])->map(fn (int $day): array => [
            'timings' => [
                'Imsak' => '04:20 (WIB)',
                'Fajr' => '04:30 (WIB)',
                'Sunrise' => '05:45 (WIB)',
                'Dhuhr' => '11:55 (WIB)',
                'Asr' => '15:15 (WIB)',
                'Maghrib' => '18:05 (WIB)',
                'Isha' => '19:15 (WIB)',
            ],
            'date' => ['gregorian' => ['date' => sprintf('%02d-%s-%s', $day, $month, $year)]],
        ])->all(),
    ];
}

it('menyimpan jadwal sholat dari API ke database', function (): void {
    Http::fake(['*' => Http::response(aladhanResponse('09', '2026'))]);

    $synced = app(PrayerScheduleService::class)->syncMonth(2026, 9);

    expect($synced)->toBe(2)
        ->and(PrayerSchedule::query()->count())->toBe(2);

    $schedule = PrayerSchedule::query()->whereDate('date', '2026-09-01')->firstOrFail();

    expect($schedule->fajr)->toBe('04:30:00')
        ->and($schedule->maghrib)->toBe('18:05:00')
        ->and($schedule->is_override)->toBeFalse();
});

it('tidak menimpa jadwal yang dikunci pengurus', function (): void {
    PrayerSchedule::factory()->override()->create([
        'date' => '2026-09-01',
        'fajr' => '04:45:00',
    ]);

    Http::fake(['*' => Http::response(aladhanResponse('09', '2026'))]);

    app(PrayerScheduleService::class)->syncMonth(2026, 9);

    expect(PrayerSchedule::query()->whereDate('date', '2026-09-01')->firstOrFail()->fajr)->toBe('04:45:00');
});

it('melempar kesalahan yang bisa dibaca ketika API gagal', function (): void {
    Http::fake(['*' => Http::response(status: 503)]);

    expect(fn () => app(PrayerScheduleService::class)->syncMonth(2026, 9))
        ->toThrow(RuntimeException::class, 'Gagal mengambil jadwal sholat dari API');
});

it('mempertahankan jadwal lama ketika sinkronisasi gagal', function (): void {
    PrayerSchedule::factory()->create(['date' => today(), 'fajr' => '04:31:00']);

    Http::fake(['*' => Http::response(status: 500)]);

    $this->artisan('masjid:sync-prayer-schedules', ['--months' => 0])->assertFailed();

    expect(PrayerSchedule::query()->whereDate('date', today())->firstOrFail()->fajr)->toBe('04:31:00');
});

it('menentukan waktu sholat berikutnya pada hari yang sama', function (): void {
    PrayerSchedule::factory()->create(['date' => today()]);

    $this->travelTo(today()->setTime(12, 30));

    $next = app(PrayerScheduleService::class)->nextPrayer();

    expect($next['key'])->toBe('asr')
        ->and($next['label'])->toBe('Ashar');
});

it('beralih ke Subuh esok hari setelah Isya berlalu', function (): void {
    PrayerSchedule::factory()->create(['date' => today()]);
    PrayerSchedule::factory()->create(['date' => today()->addDay(), 'fajr' => '04:33:00']);

    $this->travelTo(today()->setTime(22, 0));

    $next = app(PrayerScheduleService::class)->nextPrayer();

    expect($next['key'])->toBe('fajr')
        ->and($next['time']->toDateString())->toBe(today()->addDay()->toDateString());
});

it('mengembalikan null ketika jadwal belum tersedia', function (): void {
    expect(app(PrayerScheduleService::class)->nextPrayer())->toBeNull();
});

it('menyimpan langganan push notification jamaah', function (): void {
    $this->postJson('/push/langganan', [
        'endpoint' => 'https://push.example.com/langganan/abc123',
        'keys' => ['p256dh' => 'kunci-publik-contoh', 'auth' => 'token-auth-contoh'],
        'prayers' => ['fajr', 'maghrib'],
        'minutes_before' => 15,
    ])->assertSuccessful();

    $subscription = PushSubscription::query()->firstOrFail();

    expect($subscription->enabled_prayers)->toBe(['fajr', 'maghrib'])
        ->and($subscription->minutes_before)->toBe(15)
        ->and($subscription->wantsPrayer('fajr'))->toBeTrue()
        ->and($subscription->wantsPrayer('dhuhr'))->toBeFalse();
});

it('tidak menduplikasi langganan untuk endpoint yang sama', function (): void {
    $payload = [
        'endpoint' => 'https://push.example.com/langganan/abc123',
        'keys' => ['p256dh' => 'kunci', 'auth' => 'token'],
    ];

    $this->postJson('/push/langganan', $payload)->assertSuccessful();
    $this->postJson('/push/langganan', [...$payload, 'minutes_before' => 30])->assertSuccessful();

    expect(PushSubscription::query()->count())->toBe(1)
        ->and(PushSubscription::query()->firstOrFail()->minutes_before)->toBe(30);
});

it('menghapus langganan saat jamaah berhenti berlangganan', function (): void {
    $endpoint = 'https://push.example.com/langganan/abc123';

    $this->postJson('/push/langganan', [
        'endpoint' => $endpoint,
        'keys' => ['p256dh' => 'kunci', 'auth' => 'token'],
    ])->assertSuccessful();

    $this->deleteJson('/push/langganan', ['endpoint' => $endpoint])->assertSuccessful();

    expect(PushSubscription::query()->count())->toBe(0);
});

it('menolak langganan dengan waktu sholat yang tidak dikenal', function (): void {
    $this->postJson('/push/langganan', [
        'endpoint' => 'https://push.example.com/langganan/abc123',
        'keys' => ['p256dh' => 'kunci', 'auth' => 'token'],
        'prayers' => ['sholat-dhuha'],
    ])->assertJsonValidationErrors('prayers.0');
});

it('menyusun isi notifikasi pengingat dari jadwal terdekat', function (): void {
    PrayerSchedule::factory()->create(['date' => today()]);

    $this->travelTo(today()->setTime(17, 55));

    $this->getJson('/push/konten')
        ->assertSuccessful()
        ->assertJsonPath('title', 'Menjelang Maghrib')
        ->assertJsonFragment(['url' => route('jadwal-sholat')]);
});
