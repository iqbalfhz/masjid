<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Kalender & detail kegiatan tahunan (PRD 5.1.3).
 */
class EventController extends Controller
{
    public function index(Request $request): View
    {
        $showPast = $request->boolean('arsip');

        $events = Event::query()
            ->approved()
            ->with('tags')
            ->when($request->filled('kategori'), fn ($query) => $query->where('category', $request->string('kategori')->toString()))
            ->when(
                $showPast,
                fn ($query) => $query->whereDate('event_date', '<', today())->orderByDesc('event_date'),
                fn ($query) => $query->whereDate('event_date', '>=', today())->orderBy('event_date'),
            )
            ->paginate(9)
            ->withQueryString();

        return view('public.kegiatan-index', [
            'events' => $events,
            'showPast' => $showPast,
            'categories' => Event::query()->approved()->whereNotNull('category')->distinct()->pluck('category'),
        ]);
    }

    public function show(Event $event): View
    {
        abort_unless($event->isApproved(), 404);

        return view('public.kegiatan-detail', [
            'event' => $event->load('tags'),
            'rsvpCount' => $event->rsvps()->sum('person_count'),
        ]);
    }
}
