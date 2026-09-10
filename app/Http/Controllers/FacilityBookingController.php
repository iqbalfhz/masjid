<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFacilityBookingRequest;
use App\Models\Facility;
use App\Models\FacilityBooking;
use App\Services\AdminNotifier;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Peminjaman fasilitas masjid (PRD 5.1.14): form pengajuan, kalender
 * ketersediaan, dan pengecekan status lewat nomor pengajuan.
 */
class FacilityBookingController extends Controller
{
    public function create(): View
    {
        return view('public.peminjaman-fasilitas', [
            'facilities' => Facility::query()->where('is_active', true)->orderBy('name')->get(),
            'upcomingBookings' => FacilityBooking::query()
                ->blocking()
                ->with('facility')
                ->whereDate('booking_date', '>=', today())
                ->orderBy('booking_date')
                ->orderBy('start_time')
                ->take(20)
                ->get(),
        ]);
    }

    /**
     * Slot terpakai dalam format JSON, dipakai kalender ketersediaan di halaman form.
     */
    public function calendar(Request $request): JsonResponse
    {
        $bookings = FacilityBooking::query()
            ->blocking()
            ->when($request->filled('fasilitas'), fn ($query) => $query->where('facility_id', $request->integer('fasilitas')))
            ->whereDate('booking_date', '>=', today())
            ->whereDate('booking_date', '<=', today()->addMonths(3))
            ->get()
            ->map(fn (FacilityBooking $booking): array => [
                'tanggal' => $booking->booking_date->toDateString(),
                'mulai' => substr((string) $booking->start_time, 0, 5),
                'selesai' => substr((string) $booking->end_time, 0, 5),
                'fasilitas' => $booking->facility_id,
            ]);

        return response()->json($bookings);
    }

    public function store(StoreFacilityBookingRequest $request, AdminNotifier $notifier): RedirectResponse
    {
        $booking = FacilityBooking::query()->create($request->validated());

        $notifier->newFacilityBooking(
            'Pengajuan peminjaman fasilitas baru',
            "{$booking->name} mengajukan {$booking->facility->name} pada {$booking->booking_date->translatedFormat('d F Y')} ({$booking->booking_number}).",
            route('filament.admin.resources.facility-bookings.index'),
        );

        return back()
            ->with('status', 'Pengajuan Anda diterima dan sedang ditinjau pengurus. Simpan nomor pengajuan untuk mengecek statusnya.')
            ->with('reference', $booking->booking_number);
    }

    public function status(Request $request): View
    {
        $number = $request->string('nomor')->trim()->toString();

        return view('public.peminjaman-status', [
            'number' => $number,
            'booking' => $number === ''
                ? null
                : FacilityBooking::query()->with('facility')->where('booking_number', $number)->first(),
        ]);
    }
}
