<?php

namespace App\Filament\Support;

use App\Enums\ContentStatus;
use App\Models\User;
use App\Services\AdminNotifier;
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
 */
class ApprovalActions
{
    /**
     * @param  callable(Model): string  $titleResolver
     * @param  callable(Model): string  $urlResolver
     * @return list<Action>
     */
    public static function make(string $moduleLabel, callable $titleResolver, callable $urlResolver): array
    {
        return [
            static::submit($moduleLabel, $titleResolver, $urlResolver),
            static::approve($moduleLabel, $titleResolver, $urlResolver),
            static::reject($moduleLabel, $titleResolver, $urlResolver),
        ];
    }

    /**
     * @param  callable(Model): string  $titleResolver
     * @param  callable(Model): string  $urlResolver
     */
    public static function submit(string $moduleLabel, callable $titleResolver, callable $urlResolver): Action
    {
        return Action::make('submitForApproval')
            ->label('Ajukan Approval')
            ->icon('heroicon-o-paper-airplane')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Ajukan konten untuk ditinjau')
            ->modalDescription('Konten akan dikunci dari perubahan dan diteruskan ke Ketua DKM untuk ditinjau.')
            ->visible(fn (Model $record): bool => in_array($record->status, [ContentStatus::Draft, ContentStatus::Ditolak], true)
                && Auth::user()?->can('update', $record) === true)
            ->action(function (Model $record) use ($moduleLabel, $titleResolver, $urlResolver): void {
                $record->submitForApproval();

                activity($record->activityLogName())
                    ->performedOn($record)
                    ->causedBy(Auth::user())
                    ->event('submitted')
                    ->log('Mengajukan approval '.$moduleLabel);

                app(AdminNotifier::class)->contentAwaitingApproval(
                    $record,
                    $moduleLabel,
                    $titleResolver($record),
                    $urlResolver($record),
                );

                Notification::make()
                    ->title('Berhasil diajukan')
                    ->body('Ketua DKM sudah menerima notifikasi untuk meninjau konten ini.')
                    ->success()
                    ->send();
            });
    }

    /**
     * @param  callable(Model): string  $titleResolver
     * @param  callable(Model): string  $urlResolver
     */
    public static function approve(string $moduleLabel, callable $titleResolver, callable $urlResolver): Action
    {
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
            ->visible(fn (Model $record): bool => $record->status === ContentStatus::MenungguApproval
                && Auth::user()?->can('approve', $record) === true)
            ->action(function (Model $record, array $data) use ($moduleLabel, $titleResolver, $urlResolver): void {
                /** @var User $reviewer */
                $reviewer = Auth::user();
                $note = $data['approval_note'] ?? null;

                $record->approveBy($reviewer, $note);

                activity($record->activityLogName())
                    ->performedOn($record)
                    ->causedBy($reviewer)
                    ->event('approved')
                    ->log('Menyetujui '.$moduleLabel);

                app(AdminNotifier::class)->contentReviewed(
                    $record,
                    $moduleLabel,
                    $titleResolver($record),
                    $urlResolver($record),
                    $reviewer,
                    approved: true,
                    note: $note,
                );

                Notification::make()->title('Konten disetujui dan sudah tayang')->success()->send();
            });
    }

    /**
     * @param  callable(Model): string  $titleResolver
     * @param  callable(Model): string  $urlResolver
     */
    public static function reject(string $moduleLabel, callable $titleResolver, callable $urlResolver): Action
    {
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
            ->visible(fn (Model $record): bool => $record->status === ContentStatus::MenungguApproval
                && Auth::user()?->can('approve', $record) === true)
            ->action(function (Model $record, array $data) use ($moduleLabel, $titleResolver, $urlResolver): void {
                /** @var User $reviewer */
                $reviewer = Auth::user();
                $note = $data['approval_note'];

                $record->rejectBy($reviewer, $note);

                activity($record->activityLogName())
                    ->performedOn($record)
                    ->causedBy($reviewer)
                    ->event('rejected')
                    ->log('Menolak '.$moduleLabel);

                app(AdminNotifier::class)->contentReviewed(
                    $record,
                    $moduleLabel,
                    $titleResolver($record),
                    $urlResolver($record),
                    $reviewer,
                    approved: false,
                    note: $note,
                );

                Notification::make()->title('Konten ditolak dan dikembalikan ke pembuatnya')->warning()->send();
            });
    }
}
