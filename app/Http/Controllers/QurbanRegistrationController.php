<?php

namespace App\Http\Controllers;

use App\Enums\QurbanServiceType;
use App\Http\Requests\StoreQurbanRegistrationRequest;
use App\Models\QurbanRegistration;
use App\Services\AdminNotifier;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Pendaftaran kurban & aqiqah (PRD 5.1.12). Pembayaran manual: jamaah menerima
 * nomor pendaftaran sebagai bukti, lalu transfer dan konfirmasi ke bendahara.
 */
class QurbanRegistrationController extends Controller
{
    public function create(): View
    {
        return view('public.layanan-kurban', [
            'animalTypes' => QurbanRegistration::ANIMAL_TYPES,
            'serviceTypes' => collect(QurbanServiceType::cases())
                ->mapWithKeys(fn (QurbanServiceType $case): array => [$case->value => $case->getLabel()])
                ->all(),
        ]);
    }

    public function store(StoreQurbanRegistrationRequest $request, AdminNotifier $notifier): RedirectResponse
    {
        $registration = QurbanRegistration::query()->create($request->validated());

        $notifier->newRegistration(
            'kurban & aqiqah',
            'Pendaftaran kurban baru',
            "{$registration->name} mendaftar {$registration->quantity} {$registration->animalLabel()} ({$registration->registration_number}).",
            route('filament.admin.resources.qurban-registrations.index'),
        );

        return back()
            ->with('status', 'Pendaftaran Anda tercatat. Silakan transfer ke rekening panitia lalu konfirmasi ke bendahara dengan menyebut nomor pendaftaran.')
            ->with('reference', $registration->registration_number);
    }
}
