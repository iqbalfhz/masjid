<?php

namespace App\Filament\Support;

use App\Enums\ContentStatus;
use App\Models\User;
use App\Services\AdminNotifier;
use App\Support\ApprovableModules;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Aksi alur approval yang dipakai bersama oleh Pengumuman, Kajian, Kegiatan,
 * dan Artikel (PRD 5.2 & 5.2.17).
 *
 * Setiap keputusan menulis jejak reviewer ke record, mencatat Log Aktivitas,
 * dan mengirim notifikasi internal ke pihak yang perlu tahu.
 *
 * Sebutan modul, kolom judul, dan rute halaman ubah diturunkan dari
 * ApprovableModules — bukan dioper tiap pemanggil — supaya aksi ini bisa
 * dipakai apa adanya baik di tabel resource maupun di antrean dashboard, yang
 * mencampur keempat modul dalam satu tabel.
 */
class ApprovalActions
{
    /**
     * @return list<Action>
     */
    /**
     * Resolver record opsional.
     *
     * Tabel resource menyuntikkan model Eloquent apa adanya, sedangkan antrean
     * dashboard bekerja dengan record array — satu-satunya bentuk yang boleh
     * membawa kunci sendiri saat tabel mencampur beberapa model. Dengan resolver
     * ini definisi aksinya tetap satu, bukan disalin untuk dashboard.
     *
     *  list<Action>
     */
    public static function make(?Closure $resolveRecord = null): array
    {
        return [
            static::submit($resolveRecord),
            static::approve($resolveRecord),
            static::reject($resolveRecord),
        ];
    }

    private static function resolver(?Closure $resolveRecord): Closure
    {
        return $resolveRecord ?? static fn (mixed $record): Model => $record;
    }

    public static function submit(?Closure $resolveRecord = null): Action
    {
        $toModel = static::resolver($resolveRecord);

        return Action::make('submitForApproval')
            ->label('Ajukan Approval')
            ->icon('heroicon-o-paper-airplane')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Ajukan konten untuk ditinjau')
            ->modalDescription('Konten akan dikunci dari perubahan dan diteruskan ke Ketua DKM untuk ditinjau.')
            ->visible(function (mixed $record) use ($toModel): bool {
                $record = $toModel($record);

                return in_array($record->status, [ContentStatus::Draft, ContentStatus::Ditolak], true)
                    && Auth::user()?->can('update', $record) === true;
            })
            ->action(function (mixed $record) use ($toModel): void {
                $record = $toModel($record);
                $moduleLabel = ApprovableModules::labelFor($record);

                $record->submitForApproval();

                activity($record->activityLogName())
                    ->performedOn($record)
                    ->causedBy(Auth::user())
                    ->event('submitted')
                    ->log('Mengajukan approval '.$moduleLabel);

                app(AdminNotifier::class)->contentAwaitingApproval(
                    $record,
                    $moduleLabel,
                    ApprovableModules::titleFor($record),
                    ApprovableModules::urlFor($record),
                );

                Notification::make()
                    ->title('Berhasil diajukan')
                    ->body('Ketua DKM sudah menerima notifikasi untuk meninjau konten ini.')
                    ->success()
                    ->send();
            });
    }

    public static function approve(?Closure $resolveRecord = null): Action
    {
        $toModel = static::resolver($resolveRecord);

        return Action::make('approve')
            ->label('Setujui')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Setujui konten ini?')
            ->modalDescription('Setelah disetujui, konten langsung tayang di website publik.')
            ->schema([
                Textarea::make('approval_note')
                    ->label('Catatan (opsional)')
                    ->rows(2),
            ])
            ->visible(function (mixed $record) use ($toModel): bool {
                $record = $toModel($record);

                return $record->status === ContentStatus::MenungguApproval
                    && Auth::user()?->can('approve', $record) === true;
            })
            ->action(function (mixed $record, array $data) use ($toModel): void {
                $record = $toModel($record);
                /** @var User $reviewer */
                $reviewer = Auth::user();
                $note = $data['approval_note'] ?? null;
                $moduleLabel = ApprovableModules::labelFor($record);

                $record->approveBy($reviewer, $note);

                activity($record->activityLogName())
                    ->performedOn($record)
                    ->causedBy($reviewer)
                    ->event('approved')
                    ->log('Menyetujui '.$moduleLabel);

                app(AdminNotifier::class)->contentReviewed(
                    $record,
                    $moduleLabel,
                    ApprovableModules::titleFor($record),
                    ApprovableModules::urlFor($record),
                    $reviewer,
                    approved: true,
                    note: $note,
                );

                Notification::make()->title('Konten disetujui dan sudah tayang')->success()->send();
            });
    }

    public static function reject(?Closure $resolveRecord = null): Action
    {
        $toModel = static::resolver($resolveRecord);

        return Action::make('reject')
            ->label('Tolak')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->modalHeading('Tolak konten ini?')
            ->modalDescription('Konten dikembalikan ke pembuatnya beserta catatan revisi.')
            ->schema([
                Textarea::make('approval_note')
                    ->label('Alasan penolakan / catatan revisi')
                    ->required()
                    ->rows(3),
            ])
            ->visible(function (mixed $record) use ($toModel): bool {
                $record = $toModel($record);

                return $record->status === ContentStatus::MenungguApproval
                    && Auth::user()?->can('approve', $record) === true;
            })
            ->action(function (mixed $record, array $data) use ($toModel): void {
                $record = $toModel($record);
                /** @var User $reviewer */
                $reviewer = Auth::user();
                $note = $data['approval_note'];
                $moduleLabel = ApprovableModules::labelFor($record);

                $record->rejectBy($reviewer, $note);

                activity($record->activityLogName())
                    ->performedOn($record)
                    ->causedBy($reviewer)
                    ->event('rejected')
                    ->log('Menolak '.$moduleLabel);

                app(AdminNotifier::class)->contentReviewed(
                    $record,
                    $moduleLabel,
                    ApprovableModules::titleFor($record),
                    ApprovableModules::urlFor($record),
                    $reviewer,
                    approved: false,
                    note: $note,
                );

                Notification::make()->title('Konten ditolak dan dikembalikan ke pembuatnya')->warning()->send();
            });
    }
}
