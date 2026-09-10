<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTestimonialRequest;
use App\Models\Testimonial;
use App\Services\AdminNotifier;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Buku tamu & testimoni jamaah (PRD 5.1.10). Setiap kiriman masuk dengan
 * status "menunggu" dan baru tampil setelah dimoderasi Sekretaris.
 */
class TestimonialController extends Controller
{
    public function index(): View
    {
        return view('public.testimoni', [
            'testimonials' => Testimonial::query()->approved()->latest()->paginate(12),
        ]);
    }

    public function store(StoreTestimonialRequest $request, AdminNotifier $notifier): RedirectResponse
    {
        $testimonial = Testimonial::query()->create([
            ...$request->safe()->only(['name', 'message']),
            'ip_address' => $request->ip(),
        ]);

        $notifier->newJamaahInput(
            'Testimoni baru menunggu moderasi',
            str($testimonial->message)->limit(120)->value(),
            route('filament.admin.resources.testimonials.index'),
        );

        return back()->with('status', 'Terima kasih! Pesan Anda sudah kami terima dan akan tampil setelah ditinjau pengurus.');
    }
}
