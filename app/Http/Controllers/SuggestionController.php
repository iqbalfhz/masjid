<?php

namespace App\Http\Controllers;

use App\Enums\SuggestionCategory;
use App\Http\Requests\StoreSuggestionRequest;
use App\Models\Suggestion;
use App\Services\AdminNotifier;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Kotak saran & pengaduan (PRD 5.1.11). Jamaah boleh mengirim anonim; yang
 * mengisi kontak bisa dihubungi kembali oleh pengurus.
 */
class SuggestionController extends Controller
{
    public function create(): View
    {
        return view('public.kotak-saran', [
            'categories' => collect(SuggestionCategory::cases())
                ->mapWithKeys(fn (SuggestionCategory $case): array => [$case->value => $case->getLabel()])
                ->all(),
        ]);
    }

    public function store(StoreSuggestionRequest $request, AdminNotifier $notifier): RedirectResponse
    {
        $suggestion = Suggestion::query()->create($request->validated());

        $notifier->newJamaahInput(
            'Masukan baru dari jamaah',
            "[{$suggestion->category->getLabel()}] ".str($suggestion->message)->limit(120)->value(),
            route('filament.admin.resources.suggestions.index'),
        );

        return back()
            ->with('status', 'Masukan Anda sudah kami terima. Terima kasih telah membantu memperbaiki pelayanan masjid.')
            ->with('reference', $suggestion->ticket_code);
    }
}
