<?php

use App\Enums\ContentStatus;
use App\Enums\UserRole;
use App\Models\Announcement;
use App\Models\Article;
use App\Services\AdminNotifier;
use Spatie\Activitylog\Models\Activity;

beforeEach(function (): void {
    seedRoles();
});

it('memindahkan draft ke antrean approval', function (): void {
    $announcement = Announcement::factory()->create();

    $announcement->submitForApproval();

    expect($announcement->fresh()->status)->toBe(ContentStatus::MenungguApproval);
});

it('membersihkan jejak penolakan sebelumnya ketika draft diajukan ulang', function (): void {
    $reviewer = userWithRole(UserRole::KetuaDkm);
    $announcement = Announcement::factory()->awaitingApproval()->create();
    $announcement->rejectBy($reviewer, 'Judulnya kurang jelas.');

    $announcement->fresh()->submitForApproval();
    $announcement = $announcement->fresh();

    expect($announcement->status)->toBe(ContentStatus::MenungguApproval)
        ->and($announcement->approval_note)->toBeNull()
        ->and($announcement->reviewed_by)->toBeNull()
        ->and($announcement->reviewed_at)->toBeNull();
});

it('mencatat siapa yang menyetujui beserta waktunya', function (): void {
    $reviewer = userWithRole(UserRole::KetuaDkm);
    $announcement = Announcement::factory()->awaitingApproval()->create();

    $announcement->approveBy($reviewer, 'Sudah sesuai.');
    $announcement = $announcement->fresh();

    expect($announcement->status)->toBe(ContentStatus::Disetujui)
        ->and($announcement->reviewed_by)->toBe($reviewer->id)
        ->and($announcement->reviewed_at)->not->toBeNull()
        ->and($announcement->approval_note)->toBe('Sudah sesuai.');
});

it('mencatat alasan penolakan beserta reviewernya', function (): void {
    $reviewer = userWithRole(UserRole::KetuaDkm);
    $article = Article::factory()->awaitingApproval()->create();

    $article->rejectBy($reviewer, 'Mohon lengkapi rujukan dalilnya.');
    $article = $article->fresh();

    expect($article->status)->toBe(ContentStatus::Ditolak)
        ->and($article->approval_note)->toBe('Mohon lengkapi rujukan dalilnya.')
        ->and($article->reviewer->is($reviewer))->toBeTrue();
});

it('menandai konten yang ditolak sebagai bisa disunting kembali oleh pembuatnya', function (): void {
    $reviewer = userWithRole(UserRole::KetuaDkm);
    $announcement = Announcement::factory()->awaitingApproval()->create();

    expect($announcement->isEditableByAuthor())->toBeFalse();

    $announcement->rejectBy($reviewer, 'Perlu revisi.');

    expect($announcement->fresh()->isEditableByAuthor())->toBeTrue();
});

it('mengirim notifikasi ke approver saat konten diajukan', function (): void {
    $ketua = userWithRole(UserRole::KetuaDkm);
    $sekretaris = userWithRole(UserRole::Sekretaris);
    $announcement = Announcement::factory()->create(['created_by' => $sekretaris->id]);

    app(AdminNotifier::class)->contentAwaitingApproval(
        $announcement,
        'pengumuman',
        $announcement->title,
        'http://localhost/admin',
    );

    expect($ketua->fresh()->notifications()->count())->toBe(1)
        ->and($sekretaris->fresh()->notifications()->count())->toBe(0);
});

it('mengabari pembuat konten beserta nama reviewer ketika ditolak', function (): void {
    $sekretaris = userWithRole(UserRole::Sekretaris);
    $ketua = userWithRole(UserRole::KetuaDkm, ['name' => 'Ahmad Fauzi']);
    $announcement = Announcement::factory()->awaitingApproval()->create(['created_by' => $sekretaris->id]);

    app(AdminNotifier::class)->contentReviewed(
        $announcement,
        'pengumuman',
        $announcement->title,
        'http://localhost/admin',
        $ketua,
        approved: false,
        note: 'Judul perlu diperjelas.',
    );

    $notification = $sekretaris->fresh()->notifications()->firstOrFail();

    expect($notification->data['body'])
        ->toContain('Ahmad Fauzi')
        ->toContain('Judul perlu diperjelas.');
});

it('tidak mengirim notifikasi hasil review bila pembuatnya tidak diketahui', function (): void {
    $ketua = userWithRole(UserRole::KetuaDkm);
    $announcement = Announcement::factory()->awaitingApproval()->create(['created_by' => null]);

    app(AdminNotifier::class)->contentReviewed(
        $announcement,
        'pengumuman',
        $announcement->title,
        'http://localhost/admin',
        $ketua,
        approved: true,
    );

    expect($ketua->fresh()->notifications()->count())->toBe(0);
});

it('mencatat pembuatan dan perubahan konten ke log aktivitas', function (): void {
    $announcement = Announcement::factory()->create(['title' => 'Judul Awal']);

    expect(Activity::query()->where('subject_id', $announcement->id)->where('event', 'created')->exists())->toBeTrue();

    $announcement->update(['title' => 'Judul Diperbarui']);

    $updated = Activity::query()
        ->where('subject_id', $announcement->id)
        ->where('event', 'updated')
        ->firstOrFail();

    expect($updated->attribute_changes['attributes']['title'])->toBe('Judul Diperbarui')
        ->and($updated->attribute_changes['old']['title'])->toBe('Judul Awal');
});

it('tidak mencatat password pengguna ke log aktivitas', function (): void {
    $user = userWithRole(UserRole::Sekretaris);

    $user->update(['name' => 'Nama Baru']);

    $activity = Activity::query()
        ->where('subject_type', $user::class)
        ->where('event', 'updated')
        ->firstOrFail();

    expect($activity->attribute_changes['attributes'])->not->toHaveKey('password')
        ->and($activity->attribute_changes['attributes']['name'])->toBe('Nama Baru');
});

it('mengisi created_by otomatis dari user yang sedang login', function (): void {
    $sekretaris = userWithRole(UserRole::Sekretaris);

    $this->actingAs($sekretaris);

    $announcement = Announcement::query()->create([
        'title' => 'Pengumuman dari sekretaris',
        'content' => '<p>Isi pengumuman.</p>',
        'start_date' => today(),
    ]);

    expect($announcement->created_by)->toBe($sekretaris->id);
});

it('membuat slug unik ketika dua konten berjudul sama', function (): void {
    $pertama = Announcement::factory()->create(['title' => 'Kerja Bakti Masjid']);
    $kedua = Announcement::factory()->create(['title' => 'Kerja Bakti Masjid']);

    expect($pertama->slug)->toBe('kerja-bakti-masjid')
        ->and($kedua->slug)->toBe('kerja-bakti-masjid-2');
});
