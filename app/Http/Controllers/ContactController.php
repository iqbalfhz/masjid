<?php

namespace App\Http\Controllers;

use App\Models\BoardMember;
use Illuminate\Contracts\View\View;

/**
 * Kontak & lokasi masjid (PRD 5.1.19).
 */
class ContactController extends Controller
{
    public function __invoke(): View
    {
        return view('public.kontak', [
            'contacts' => BoardMember::query()->active()->take(4)->get(),
        ]);
    }
}
