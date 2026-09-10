<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Contracts\View\View;

class AnnouncementController extends Controller
{
    public function __invoke(Announcement $announcement): View
    {
        abort_unless($announcement->isApproved(), 404);

        return view('public.pengumuman-detail', [
            'announcement' => $announcement->load('creator'),
            'others' => Announcement::query()
                ->active()
                ->whereKeyNot($announcement->getKey())
                ->latest('start_date')
                ->take(4)
                ->get(),
        ]);
    }
}
