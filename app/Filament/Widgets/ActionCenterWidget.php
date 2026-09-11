<?php

namespace App\Filament\Widgets;

use App\Enums\BookingStatus;
use App\Enums\ModerationStatus;
use App\Enums\PaymentStatus;
use App\Enums\SuggestionStatus;
use App\Models\FacilityBooking;
use App\Models\QurbanRegistration;
use App\Models\Suggestion;
use App\Models\Testimonial;
use App\Models\User;
use App\Models\ZakatRegistration;
use App\Support\ApprovableModules;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

/**
 * Pekerjaan yang menunggu tindakan, disaring menurut wewenang pembacanya.
 *
 * Judul "Perlu Tindakan" menjanjikan tindakan milik orang yang sedang melihat.
 * Menampilkan kartu approval kepada Bendahara — yang menurut matriks PRD 5.3
 * hanya punya hak baca pada Pengumuman — membuat janji itu tidak ditepati, dan
 * pengurus jadi menghitung pekerjaan yang bukan urusannya.
 *
 * Setiap kartu juga membawa tautan ke daftarnya. Sebelumnya angka di sini buntu:
 * pengurus melihat "7 perlu moderasi" lalu harus mencari sendiri menunya di
 * sidebar, padahal langkah berikutnya sudah jelas.
 */
class ActionCenterWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Perlu Tindakan';

    public static function canView(): bool
    {
        return static::statsFor(Auth::user()) !== [];
    }

    protected function getStats(): array
    {
        return static::statsFor(Auth::user());
    }

    /**
     * @return list<Stat>
     */
    protected static function statsFor(?User $user): array
    {
        if (! $user instanceof User) {
            return [];
        }

        $stats = [];

        if (ApprovableModules::userCanApproveAnything($user)) {
            $awaiting = ApprovableModules::awaitingApprovalCount($user);

            $stats[] = Stat::make('Menunggu approval', $awaiting)
                ->description('Pengumuman, kajian, kegiatan & artikel')
                ->descriptionIcon('heroicon-o-clock')
                ->color($awaiting > 0 ? 'warning' : 'success')
                ->url(route('filament.admin.resources.announcements.index'));
        }

        if ($user->can('approve:facility_booking') || $user->can('update:facility_booking')) {
            $bookings = FacilityBooking::query()->where('status', BookingStatus::Menunggu)->count();

            $stats[] = Stat::make('Pengajuan fasilitas', $bookings)
                ->description('Menunggu keputusan DKM')
                ->descriptionIcon('heroicon-o-building-office-2')
                ->color($bookings > 0 ? 'warning' : 'success')
                ->url(route('filament.admin.resources.facility-bookings.index'));
        }

        $canModerateTestimonials = $user->can('moderate:testimonial');
        $canHandleSuggestions = $user->can('update:suggestion');

        if ($canModerateTestimonials || $canHandleSuggestions) {
            $testimonials = $canModerateTestimonials
                ? Testimonial::query()->where('status', ModerationStatus::Menunggu)->count()
                : 0;
            $suggestions = $canHandleSuggestions
                ? Suggestion::query()->where('status', SuggestionStatus::Baru)->count()
                : 0;

            $stats[] = Stat::make('Perlu moderasi', $testimonials + $suggestions)
                ->description("{$testimonials} testimoni, {$suggestions} masukan baru")
                ->descriptionIcon('heroicon-o-chat-bubble-left-right')
                ->color($testimonials + $suggestions > 0 ? 'warning' : 'success')
                ->url(route($canModerateTestimonials
                    ? 'filament.admin.resources.testimonials.index'
                    : 'filament.admin.resources.suggestions.index'));
        }

        if ($user->can('update:qurban_registration') || $user->can('update:zakat_registration')) {
            $unpaid = QurbanRegistration::query()->where('payment_status', PaymentStatus::BelumBayar)->count()
                + ZakatRegistration::query()->where('payment_status', PaymentStatus::BelumBayar)->count();

            $stats[] = Stat::make('Pembayaran belum lunas', $unpaid)
                ->description('Pendaftaran kurban & zakat')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color($unpaid > 0 ? 'info' : 'success')
                ->url(route('filament.admin.resources.qurban-registrations.index'));
        }

        return $stats;
    }
}
