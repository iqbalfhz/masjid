<?php

namespace App\Http\Controllers;

use App\Models\BoardMember;
use Illuminate\Contracts\View\View;

/**
 * Profil masjid & struktur pengurus (PRD 5.1.6).
 */
class MosqueProfileController extends Controller
{
    public function __invoke(): View
    {
        return view('public.profil', [
            'boardMembers' => BoardMember::query()->active()->get(),
        ]);
    }
}
