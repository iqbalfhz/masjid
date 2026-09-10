<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Models\Announcement;
use App\Models\Article;
use App\Models\Event;
use App\Models\FinanceTransaction;
use App\Models\GalleryAlbum;
use App\Models\Study;
use App\Models\Testimonial;
use App\Services\MosqueStatisticsService;
use App\Services\PrayerScheduleService;
use Illuminate\Contracts\View\View;

/**
 * Beranda (PRD 5.1.1): jadwal sholat hari ini, pengumuman, kajian & kegiatan
 * terdekat, ringkasan keuangan, konten terbaru, dan statistik pencapaian.
 */
class HomeController extends Controller
{
    public function __invoke(PrayerScheduleService $prayers, MosqueStatisticsService $statistics): View
    {
        $thisMonth = FinanceTransaction::query()->inMonth(today()->year, today()->month);

        return view('public.home', [
            'todaySchedule' => $prayers->today(),
            'nextPrayer' => $prayers->nextPrayer(),
            'announcements' => Announcement::query()->active()->latest('start_date')->take(3)->get(),
            'upcomingStudies' => Study::query()->approved()->orderBy('time')->take(4)->get(),
            'upcomingEvents' => Event::query()->approved()->upcoming()->take(3)->get(),
            'monthlyIncome' => (float) (clone $thisMonth)->where('type', TransactionType::In)->sum('amount'),
            'monthlyExpense' => (float) (clone $thisMonth)->where('type', TransactionType::Out)->sum('amount'),
            'latestArticles' => Article::query()->published()->with('category')->latest('publish_date')->take(3)->get(),
            'latestAlbums' => GalleryAlbum::query()->where('is_published', true)->with('items')->latest('event_date')->take(3)->get(),
            'testimonials' => Testimonial::query()->approved()->latest()->take(3)->get(),
            'statistics' => $statistics->highlights(),
        ]);
    }
}
