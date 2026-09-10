<?php

namespace App\Filament\Widgets;

use App\Enums\BookingStatus;
use App\Enums\ContentStatus;
use App\Enums\ScheduleType;
use App\Models\Event;
use App\Models\FacilityBooking;
use App\Models\Study;
use Illuminate\Support\Carbon;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

/**
 * Sumber data kalender kegiatan admin: kajian insidental, kegiatan, dan
 * peminjaman fasilitas. Kajian rutin mingguan diproyeksikan ke setiap tanggal
 * dalam rentang yang sedang dilihat.
 *
 * Kalender ini murni tampilan ringkasan (PRD 5.2.2), jadi seluruh aksi
 * bawaan widget dimatikan. Setiap entri membawa `url` ke halaman edit record
 * aslinya, sehingga klik pada agenda langsung membuka modul yang tepat.
 */
class ActivityCalendarWidget extends FullCalendarWidget
{
    public function config(): array
    {
        return [
            'initialView' => 'dayGridMonth',
            'headerToolbar' => [
                'left' => 'prev,next today',
                'center' => 'title',
                'right' => 'dayGridMonth,timeGridWeek,listWeek',
            ],
            'locale' => 'id',
            'firstDay' => 1,
            'editable' => false,
            'selectable' => false,
        ];
    }

    /**
     * Agenda tidak dibuat dari kalender — pembuatan record dilakukan di modul
     * masing-masing agar alur approval-nya tetap utuh.
     *
     * @return array<int, mixed>
     */
    protected function headerActions(): array
    {
        return [];
    }

    /**
     * @return array<int, mixed>
     */
    protected function modalActions(): array
    {
        return [];
    }

    /**
     * @param  array{start: string, end: string}  $info
     * @return list<array<string, mixed>>
     */
    public function fetchEvents(array $info): array
    {
        $start = Carbon::parse($info['start']);
        $end = Carbon::parse($info['end']);

        return [
            ...$this->studyEvents($start, $end),
            ...$this->activityEvents($start, $end),
            ...$this->bookingEvents($start, $end),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function studyEvents(Carbon $start, Carbon $end): array
    {
        $events = [];

        foreach (Study::query()->whereNot('status', ContentStatus::Ditolak)->get() as $study) {
            $color = $study->status === ContentStatus::Disetujui ? '#0d9488' : '#94a3b8';
            $url = route('filament.admin.resources.studies.edit', $study);

            if ($study->schedule_type === ScheduleType::Insidental) {
                if ($study->start_date === null || ! $study->start_date->between($start, $end)) {
                    continue;
                }

                $events[] = $this->entry(
                    'kajian-'.$study->id,
                    $study->theme,
                    $study->start_date->toDateString().'T'.$study->time,
                    $study->end_time ? $study->start_date->toDateString().'T'.$study->end_time : null,
                    $color,
                    $url,
                );

                continue;
            }

            // Kajian rutin: proyeksikan ke tiap hari yang cocok dalam rentang.
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                if ($date->dayOfWeek !== $study->day_of_week) {
                    continue;
                }

                $events[] = $this->entry(
                    'kajian-'.$study->id.'-'.$date->toDateString(),
                    $study->theme,
                    $date->toDateString().'T'.$study->time,
                    $study->end_time ? $date->toDateString().'T'.$study->end_time : null,
                    $color,
                    $url,
                );
            }
        }

        return $events;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function activityEvents(Carbon $start, Carbon $end): array
    {
        return Event::query()
            ->whereNot('status', ContentStatus::Ditolak)
            ->whereBetween('event_date', [$start, $end])
            ->get()
            ->map(fn (Event $event): array => $this->entry(
                'kegiatan-'.$event->id,
                $event->title,
                $event->event_date->toDateString().($event->start_time ? 'T'.$event->start_time : ''),
                $event->end_time ? $event->event_date->toDateString().'T'.$event->end_time : null,
                $event->status === ContentStatus::Disetujui ? '#c2760a' : '#94a3b8',
                route('filament.admin.resources.events.edit', $event),
            ))
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function bookingEvents(Carbon $start, Carbon $end): array
    {
        return FacilityBooking::query()
            ->blocking()
            ->with('facility')
            ->whereBetween('booking_date', [$start, $end])
            ->get()
            ->map(fn (FacilityBooking $booking): array => $this->entry(
                'fasilitas-'.$booking->id,
                $booking->facility->name.' — '.$booking->purpose,
                $booking->booking_date->toDateString().'T'.$booking->start_time,
                $booking->booking_date->toDateString().'T'.$booking->end_time,
                $booking->status === BookingStatus::Disetujui ? '#7c3aed' : '#94a3b8',
                route('filament.admin.resources.facility-bookings.edit', $booking),
            ))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function entry(string $id, string $title, string $start, ?string $end, string $color, string $url): array
    {
        return [
            'id' => $id,
            'title' => $title,
            'start' => $start,
            'end' => $end,
            'backgroundColor' => $color,
            'borderColor' => $color,
            // FullCalendar menavigasi ke url ini alih-alih membuka modal.
            'url' => $url,
        ];
    }
}
