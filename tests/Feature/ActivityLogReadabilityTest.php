<?php

use App\Enums\ContentStatus;
use App\Enums\UserRole;
use App\Models\Announcement;
use App\Models\Article;
use App\Models\FinanceTransaction;
use App\Models\PrayerSchedule;
use App\Support\ActivityLogPresenter;
use Illuminate\Auth\Events\Login;
use Spatie\Activitylog\Models\Activity;

beforeEach(function (): void {
    seedRoles();
    $this->presenter = app(ActivityLogPresenter::class);
});

it('memakai nama modul berbahasa Indonesia', function (): void {
    Article::factory()->create();

    $activity = Activity::query()->latest('id')->firstOrFail();

    expect($this->presenter->moduleLabel($activity))->toBe('Artikel');
});

it('memakai kunci modul yang konsisten walau nama modelnya dua kata', function (): void {
    PrayerSchedule::factory()->create();

    $activity = Activity::query()->latest('id')->firstOrFail();

    expect($activity->log_name)->toBe('prayer_schedule')
        ->and($this->presenter->moduleLabel($activity))->toBe('Jadwal Sholat');
});

it('menerjemahkan aksi ke bahasa Indonesia', function (): void {
    expect($this->presenter->eventLabel('created'))->toBe('Ditambahkan')
        ->and($this->presenter->eventLabel('updated'))->toBe('Diubah')
        ->and($this->presenter->eventLabel('approved'))->toBe('Disetujui')
        ->and($this->presenter->eventLabel('login'))->toBe('Masuk');
});

it('menampilkan judul record, bukan nomor teknis', function (): void {
    $article = Article::factory()->create(['title' => 'Keutamaan Sholat Berjamaah']);

    $activity = Activity::query()->latest('id')->firstOrFail();

    expect($this->presenter->recordLabel($activity))->toBe('Keutamaan Sholat Berjamaah')
        ->and($this->presenter->recordLabel($activity))->not->toContain('#'.$article->id);
});

it('memberi tahu ketika record aslinya sudah dihapus', function (): void {
    $article = Article::factory()->create();
    $article->delete();

    $activity = Activity::query()->where('event', 'created')->latest('id')->firstOrFail();

    expect($this->presenter->recordLabel($activity))->toBe('Artikel yang sudah dihapus');
});

it('menerjemahkan nama kolom dan nilainya', function (): void {
    $article = Article::factory()->create(['views' => 0, 'status' => ContentStatus::Draft]);
    $article->update(['views' => 1, 'status' => ContentStatus::Disetujui]);

    $activity = Activity::query()->where('event', 'updated')->latest('id')->firstOrFail();
    $changes = collect($this->presenter->changes($activity))->keyBy('kolom');

    expect($changes)->toHaveKey('Jumlah dibaca')
        ->and($changes['Jumlah dibaca']['sebelum'])->toBe('0')
        ->and($changes['Jumlah dibaca']['sesudah'])->toBe('1')
        ->and($changes)->toHaveKey('Status')
        // Enum dibaca dari cast model, jadi labelnya ikut benar.
        ->and($changes['Status']['sebelum'])->toBe('Draft')
        ->and($changes['Status']['sesudah'])->toBe('Disetujui');
});

it('menampilkan nominal keuangan sebagai rupiah', function (): void {
    $transaction = FinanceTransaction::factory()->income()->create(['amount' => 500000]);
    $transaction->update(['amount' => 750000]);

    $activity = Activity::query()->where('event', 'updated')->latest('id')->firstOrFail();
    $changes = collect($this->presenter->changes($activity))->keyBy('kolom');

    expect($changes['Nominal']['sebelum'])->toBe('Rp 500.000')
        ->and($changes['Nominal']['sesudah'])->toBe('Rp 750.000');
});

it('menampilkan nama pengguna pada kolom yang berisi id pengurus', function (): void {
    $reviewer = userWithRole(UserRole::KetuaDkm, ['name' => 'Ahmad Fauzi']);
    $announcement = Announcement::factory()->awaitingApproval()->create();

    $announcement->approveBy($reviewer);

    $activity = Activity::query()->where('event', 'updated')->latest('id')->firstOrFail();
    $changes = collect($this->presenter->changes($activity))->keyBy('kolom');

    expect($changes['Ditinjau oleh']['sesudah'])->toBe('Ahmad Fauzi');
});

it('menyembunyikan kolom teknis dari rincian perubahan', function (): void {
    $article = Article::factory()->create();
    $article->update(['title' => 'Judul Baru']);

    $activity = Activity::query()->where('event', 'updated')->latest('id')->firstOrFail();
    $kolom = collect($this->presenter->changes($activity))->pluck('kolom');

    expect($kolom)->not->toContain('Slug')
        ->and($kolom)->not->toContain('Updated at');
});

it('menandai nilai kosong secara eksplisit', function (): void {
    expect($this->presenter->formatValue(null, 'title', null))->toBe('(kosong)')
        ->and($this->presenter->formatValue(null, 'title', ''))->toBe('(kosong)');
});

it('mencatat login pengurus ke log aktivitas', function (): void {
    $user = userWithRole(UserRole::Sekretaris);

    event(new Login('web', $user, false));

    $activity = Activity::query()->where('event', 'login')->latest('id')->firstOrFail();

    expect($activity->causer->is($user))->toBeTrue()
        ->and($this->presenter->eventLabel($activity->event))->toBe('Masuk')
        ->and($activity->description)->toBe('Masuk ke admin panel');
});

it('menyusun ringkasan perubahan yang ringkas untuk tabel', function (): void {
    $article = Article::factory()->create(['views' => 0]);
    $article->update(['views' => 5]);

    $activity = Activity::query()->where('event', 'updated')->latest('id')->firstOrFail();

    expect($this->presenter->changeSummary($activity))->toBe('Jumlah dibaca: 0 → 5');
});

it('tidak menampilkan pembanding palsu pada data yang baru ditambahkan', function (): void {
    Article::factory()->create(['title' => 'Artikel Baru']);

    $activity = Activity::query()->where('event', 'created')->latest('id')->firstOrFail();

    expect($this->presenter->isComparison($activity))->toBeFalse()
        ->and($this->presenter->changesHeading($activity))->toBe('Isian yang Tercatat')
        ->and($this->presenter->changeSummary($activity))->toContain('isian tercatat')
        ->and(collect($this->presenter->changes($activity))->pluck('sebelum')->unique()->all())->toBe(['—']);

    $judul = collect($this->presenter->changes($activity))->firstWhere('kolom', 'Judul');
    expect($judul['sesudah'])->toBe('Artikel Baru');
});

it('menandai rincian sebagai perbandingan hanya pada aksi diubah', function (): void {
    $article = Article::factory()->create();
    $article->update(['title' => 'Judul Diperbarui']);

    $activity = Activity::query()->where('event', 'updated')->latest('id')->firstOrFail();

    expect($this->presenter->isComparison($activity))->toBeTrue()
        ->and($this->presenter->changesHeading($activity))->toBe('Rincian Perubahan');
});
