<?php

namespace App\Http\Controllers;

use App\Enums\MaterialType;
use App\Models\LibraryMaterial;
use App\Models\Study;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * E-Library: arsip materi kajian yang sudah lewat (PRD 5.1.9).
 */
class LibraryController extends Controller
{
    public function __invoke(Request $request): View
    {
        $keyword = $request->string('q')->trim()->toString();

        $materials = LibraryMaterial::query()
            ->with('study')
            ->when($request->filled('jenis'), fn ($query) => $query->where('type', $request->string('jenis')->toString()))
            ->when($request->filled('kajian'), fn ($query) => $query->where('study_id', $request->integer('kajian')))
            ->when($keyword !== '', fn ($query) => $query->where(
                fn ($q) => $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('ustadz_name', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%")
            ))
            ->latest('material_date')
            ->paginate(12)
            ->withQueryString();

        return view('public.e-library', [
            'materials' => $materials,
            'types' => MaterialType::cases(),
            'studies' => Study::query()->approved()->orderBy('theme')->get(),
            'keyword' => $keyword,
        ]);
    }

    /**
     * Hitung akses materi lalu arahkan ke berkas atau tautan aslinya.
     */
    public function download(LibraryMaterial $material): RedirectResponse
    {
        $url = $material->url();

        abort_if($url === null, 404);

        $material->increment('downloads');

        /*
         * Materi bisa berupa tautan eksternal (YouTube, Drive) atau berkas milik
         * masjid sendiri. Berkas lokal memakai URL relatif, jadi diarahkan lewat
         * to() agar dilengkapi host aplikasi; tautan eksternal harus lewat away()
         * supaya tidak ikut diberi prefix.
         */
        return str_starts_with($url, 'http')
            ? redirect()->away($url)
            : redirect()->to($url);
    }
}
