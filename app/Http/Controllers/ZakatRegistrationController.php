<?php

namespace App\Http\Controllers;

use App\Enums\ZakatType;
use App\Http\Requests\StoreZakatRegistrationRequest;
use App\Models\ZakatRegistration;
use App\Services\AdminNotifier;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Pendaftaran zakat fitrah & maal (PRD 5.1.13), pembayaran manual seperti kurban.
 */
class ZakatRegistrationController extends Controller
{
    public function create(): View
    {
        return view('public.layanan-zakat', [
            'zakatTypes' => collect(ZakatType::cases())
                ->mapWithKeys(fn (ZakatType $case): array => [$case->value => $case->getLabel()])
                ->all(),
        ]);
    }

    public function store(StoreZakatRegistrationRequest $request, AdminNotifier $notifier): RedirectResponse
    {
        $registration = ZakatRegistration::query()->create($request->validated());

        $notifier->newRegistration(
            'zakat',
            'Pendaftaran zakat baru',
            "{$registration->name} mendaftar {$registration->zakat_type->getLabel()} ({$registration->registration_number}).",
            route('filament.admin.resources.zakat-registrations.index'),
        );

        return back()
            ->with('status', 'Pendaftaran zakat Anda tercatat. Silakan tunaikan pembayaran dan konfirmasi ke bendahara dengan menyebut nomor pendaftaran.')
            ->with('reference', $registration->registration_number);
    }
}
