<?php

namespace App\Filament\Widgets;

use App\Enums\BookingStatus;
use App\Enums\ContentStatus;
use App\Enums\ModerationStatus;
use App\Enums\PaymentStatus;
use App\Enums\SuggestionStatus;
use App\Models\Announcement;
use App\Models\Article;
use App\Models\Event;
use App\Models\FacilityBooking;
use App\Models\QurbanRegistration;
use App\Models\Study;
use App\Models\Suggestion;
use App\Models\Testimonial;
use App\Models\ZakatRegistration;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Ringkasan pekerjaan yang menunggu tindakan pengurus, ditampilkan paling atas
 * di dashboard admin.
 */
class ApprovalQueueWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Perlu Tindakan';

    protected function getStats(): array
    {
        $awaitingApproval = Announcement::query()->where('status', ContentStatus::MenungguApproval)->count()
            + Study::query()->where('status', ContentStatus::MenungguApproval)->count()
            + Event::query()->where('status', ContentStatus::MenungguApproval)->count()
            + Article::query()->where('status', ContentStatus::MenungguApproval)->count();

        $pendingBookings = FacilityBooking::query()->where('status', BookingStatus::Menunggu)->count();
        $pendingTestimonials = Testimonial::query()->where('status', ModerationStatus::Menunggu)->count();
        $newSuggestions = Suggestion::query()->where('status', SuggestionStatus::Baru)->count();
        $unpaidRegistrations = QurbanRegistration::query()->where('payment_status', PaymentStatus::BelumBayar)->count()
            + ZakatRegistration::query()->where('payment_status', PaymentStatus::BelumBayar)->count();

        return [
            Stat::make('Menunggu approval', $awaitingApproval)
                ->description('Pengumuman, kajian, kegiatan & artikel')
                ->descriptionIcon('heroicon-o-clock')
                ->color($awaitingApproval > 0 ? 'warning' : 'success'),

            Stat::make('Pengajuan fasilitas', $pendingBookings)
                ->description('Menunggu keputusan DKM')
                ->descriptionIcon('heroicon-o-building-office-2')
                ->color($pendingBookings > 0 ? 'warning' : 'success'),

            Stat::make('Perlu moderasi', $pendingTestimonials + $newSuggestions)
                ->description("{$pendingTestimonials} testimoni, {$newSuggestions} masukan baru")
                ->descriptionIcon('heroicon-o-chat-bubble-left-right')
                ->color($pendingTestimonials + $newSuggestions > 0 ? 'warning' : 'success'),

            Stat::make('Pembayaran belum lunas', $unpaidRegistrations)
                ->description('Pendaftaran kurban & zakat')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color($unpaidRegistrations > 0 ? 'info' : 'success'),
        ];
    }
}
