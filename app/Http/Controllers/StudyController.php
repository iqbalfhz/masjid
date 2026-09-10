<?php

namespace App\Http\Controllers;

use App\Enums\ScheduleType;
use App\Models\Study;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Daftar & detail kajian (PRD 5.1.3).
 */
class StudyController extends Controller
{
    public function index(Request $request): View
    {
        $studies = Study::query()
            ->approved()
            ->with('tags')
            ->when($request->filled('hari'), fn ($query) => $query->where('day_of_week', $request->integer('hari')))
            ->when($request->filled('jenis'), fn ($query) => $query->where('schedule_type', $request->string('jenis')->toString()))
            ->when($request->filled('q'), fn ($query) => $query->where(
                fn ($q) => $q->where('theme', 'like', '%'.$request->string('q').'%')
                    ->orWhere('ustadz_name', 'like', '%'.$request->string('q').'%')
            ))
            ->orderBy('day_of_week')
            ->orderBy('time')
            ->paginate(12)
            ->withQueryString();

        return view('public.kajian-index', [
            'studies' => $studies,
            'days' => Study::DAYS,
            'scheduleTypes' => ScheduleType::cases(),
        ]);
    }

    public function show(Study $study): View
    {
        abort_unless($study->isApproved(), 404);

        return view('public.kajian-detail', [
            'study' => $study->load(['tags', 'materials']),
            'rsvpCount' => $study->rsvps()->sum('person_count'),
            'related' => Study::query()
                ->approved()
                ->whereKeyNot($study->getKey())
                ->where('ustadz_name', $study->ustadz_name)
                ->take(3)
                ->get(),
        ]);
    }
}
