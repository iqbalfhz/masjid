<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\ImmediateDatabaseNotification;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Notifikasi internal admin panel (PRD 5.2.17).
 *
 * Semua notifikasi disimpan sebagai database notification bawaan Filament,
 * sehingga langsung tampil di ikon lonceng dan bisa diklik menuju record.
 */
class AdminNotifier
{
    /**
     * Konten baru menunggu persetujuan → kabari para approver.
     */
    public function contentAwaitingApproval(Model $record, string $moduleLabel, string $title, string $url): void
    {
        $author = $record->creator?->name ?? 'Seorang pengurus';

        $this->send(
            $this->approvers(),
            Str::ucfirst($moduleLabel).' menunggu approval',
            "{$author} mengajukan \"{$title}\" untuk ditinjau.",
            'heroicon-o-clock',
            'warning',
            $url,
        );
    }

    /**
     * Hasil keputusan approval → kabari pembuat konten, sertakan nama reviewer
     * dan catatan revisi bila ditolak.
     */
    public function contentReviewed(Model $record, string $moduleLabel, string $title, string $url, User $reviewer, bool $approved, ?string $note = null): void
    {
        $author = $record->creator;

        if (! $author instanceof User) {
            return;
        }

        $body = $approved
            ? "\"{$title}\" disetujui oleh {$reviewer->name} dan sudah tayang."
            : "\"{$title}\" ditolak oleh {$reviewer->name}.".($note ? " Catatan: {$note}" : '');

        $this->send(
            new Collection([$author]),
            Str::ucfirst($moduleLabel).($approved ? ' disetujui' : ' ditolak'),
            $body,
            $approved ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle',
            $approved ? 'success' : 'danger',
            $url,
        );
    }

    /**
     * Pendaftaran layanan baru (kurban/aqiqah, zakat) → kabari bendahara.
     */
    public function newRegistration(string $moduleLabel, string $title, string $body, string $url): void
    {
        $this->send(
            $this->usersWithRoles([UserRole::Bendahara, UserRole::Admin, UserRole::Superadmin]),
            $title,
            $body,
            'heroicon-o-banknotes',
            'info',
            $url,
        );
    }

    /**
     * Masukan jamaah baru (testimoni, kotak saran) → kabari sekretaris.
     */
    public function newJamaahInput(string $title, string $body, string $url): void
    {
        $this->send(
            $this->usersWithRoles([UserRole::Sekretaris, UserRole::Admin, UserRole::Superadmin]),
            $title,
            $body,
            'heroicon-o-chat-bubble-left-right',
            'info',
            $url,
        );
    }

    /**
     * Pengajuan peminjaman fasilitas baru → kabari approver dan sekretaris.
     */
    public function newFacilityBooking(string $title, string $body, string $url): void
    {
        $this->send(
            $this->usersWithRoles([UserRole::KetuaDkm, UserRole::Sekretaris, UserRole::Admin, UserRole::Superadmin]),
            $title,
            $body,
            'heroicon-o-building-office-2',
            'warning',
            $url,
        );
    }

    /**
     * @return Collection<int, User>
     */
    public function approvers(): Collection
    {
        return $this->usersWithRoles([UserRole::KetuaDkm, UserRole::Admin, UserRole::Superadmin]);
    }

    /**
     * @param  list<UserRole>  $roles
     * @return Collection<int, User>
     */
    private function usersWithRoles(array $roles): Collection
    {
        return User::query()
            ->where('is_active', true)
            ->whereHas('roles', fn ($query) => $query->whereIn('name', array_map(fn (UserRole $role): string => $role->value, $roles)))
            ->get();
    }

    /**
     * Buang skema dan host dari URL tujuan.
     *
     * Notifikasi tersimpan permanen di basis data, sementara alamat absolut
     * hanya benar selama origin-nya tidak berubah. URL yang dibekukan saat
     * seeder berjalan lewat CLI memakai APP_URL, dan begitu pengurus membuka
     * panel dari host atau port lain tautannya mati.
     *
     * Jalur relatif selalu diselesaikan terhadap origin yang sedang dibuka,
     * jadi tautannya tetap hidup di mana pun aplikasi dipasang.
     */
    private function relativeUrl(string $url): string
    {
        $bagian = parse_url($url);

        if ($bagian === false) {
            return $url;
        }

        return ($bagian['path'] ?? '/')
            .(isset($bagian['query']) ? '?'.$bagian['query'] : '')
            .(isset($bagian['fragment']) ? '#'.$bagian['fragment'] : '');
    }

    /**
     * @param  Collection<int, User>  $recipients
     */
    private function send(Collection $recipients, string $title, string $body, string $icon, string $color, string $url): void
    {
        if ($recipients->isEmpty()) {
            return;
        }

        $notification = Notification::make()
            ->title($title)
            ->body($body)
            ->icon($icon)
            ->iconColor($color)
            ->actions([
                Action::make('lihat')
                    ->label('Lihat detail')
                    ->url($this->relativeUrl($url))
                    ->markAsRead(),
            ]);

        // Sengaja tidak lewat `sendToDatabase()`: metode itu mengantrekan notifikasi
        // ke queue, dan tanpa worker berjalan lonceng pengurus tak pernah terisi.
        ImmediateDatabaseNotification::deliver($notification, $recipients);
    }
}
