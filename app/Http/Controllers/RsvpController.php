<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRsvpRequest;
use App\Models\Event;
use App\Models\Rsvp;
use App\Models\Study;
use Illuminate\Http\RedirectResponse;

/**
 * Konfirmasi kehadiran jamaah untuk kajian/kegiatan (PRD 5.1.3).
 */
class RsvpController extends Controller
{
    public function __invoke(StoreRsvpRequest $request): RedirectResponse
    {
        $subject = $request->string('jenis')->toString() === 'kajian'
            ? Study::query()->findOrFail($request->integer('id'))
            : Event::query()->findOrFail($request->integer('id'));

        abort_unless($subject->isApproved() && $subject->rsvp_enabled, 404);

        if ($this->isFull($subject)) {
            return back()->withErrors([
                'person_count' => 'Mohon maaf, kuota peserta sudah penuh. Silakan hubungi panitia untuk konfirmasi.',
            ])->withInput();
        }

        Rsvp::query()->create([
            'rsvpable_type' => $subject::class,
            'rsvpable_id' => $subject->getKey(),
            ...$request->safe()->only(['name', 'phone', 'person_count', 'note']),
        ]);

        return back()->with('status', 'Konfirmasi kehadiran Anda tercatat. Sampai jumpa di masjid, insyaAllah.');
    }

    private function isFull(Study|Event $subject): bool
    {
        if ($subject->rsvp_quota === null) {
            return false;
        }

        return (int) $subject->rsvps()->sum('person_count') >= $subject->rsvp_quota;
    }
}
