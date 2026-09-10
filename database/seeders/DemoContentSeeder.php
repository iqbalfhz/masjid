<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Enums\ScheduleType;
use App\Enums\TransactionType;
use App\Enums\UserRole;
use App\Models\Announcement;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\BoardMember;
use App\Models\Event;
use App\Models\Facility;
use App\Models\FacilityBooking;
use App\Models\FinanceCategory;
use App\Models\FinanceTransaction;
use App\Models\GalleryAlbum;
use App\Models\GalleryItem;
use App\Models\LibraryMaterial;
use App\Models\QurbanRegistration;
use App\Models\Rsvp;
use App\Models\Study;
use App\Models\Suggestion;
use App\Models\Testimonial;
use App\Models\User;
use App\Models\ZakatRegistration;
use Illuminate\Database\Seeder;

/**
 * Konten contoh untuk development: kajian rutin, pengumuman, artikel, transaksi
 * keuangan setahun terakhir, dan interaksi jamaah. Hanya dijalankan di
 * environment lokal/testing.
 */
class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $sekretaris = User::query()->whereHas('roles', fn ($query) => $query->where('name', UserRole::Sekretaris->value))->firstOrFail();
        $ketua = User::query()->whereHas('roles', fn ($query) => $query->where('name', UserRole::KetuaDkm->value))->firstOrFail();
        $bendahara = User::query()->whereHas('roles', fn ($query) => $query->where('name', UserRole::Bendahara->value))->firstOrFail();

        $this->seedAnnouncements($sekretaris, $ketua);
        $this->seedStudies($sekretaris, $ketua);
        $this->seedEvents($sekretaris, $ketua);
        $this->seedFinance($bendahara);
        $this->seedArticles($sekretaris, $ketua);
        $this->seedGallery($sekretaris);
        $this->seedLibrary($sekretaris);
        $this->seedBoardMembers();
        $this->seedJamaahInteractions($ketua);
    }

    private function seedAnnouncements(User $author, User $reviewer): void
    {
        Announcement::factory()->create([
            'title' => 'Pelaksanaan Sholat Jumat Dua Gelombang',
            'content' => '<p>Mulai pekan ini sholat Jumat dilaksanakan dalam dua gelombang untuk menampung jamaah karyawan. Gelombang pertama pukul 12.00 WIB dan gelombang kedua pukul 13.00 WIB.</p>',
            'priority' => 'tinggi',
            'status' => ContentStatus::Disetujui,
            'created_by' => $author->id,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now()->subDays(3),
        ]);

        Announcement::factory()->create([
            'title' => 'Pendaftaran TPA Anak Periode Baru Dibuka',
            'content' => '<p>Pendaftaran Taman Pendidikan Al-Quran untuk anak usia 5-12 tahun telah dibuka. Silakan hubungi sekretariat DKM di lantai P3a.</p>',
            'status' => ContentStatus::Disetujui,
            'created_by' => $author->id,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now()->subDay(),
        ]);

        Announcement::factory()->awaitingApproval()->create([
            'title' => 'Kerja Bakti Bersih Masjid Akhir Pekan',
            'content' => '<p>Mengajak seluruh jamaah untuk kerja bakti membersihkan area masjid pada Sabtu pagi.</p>',
            'created_by' => $author->id,
        ]);
    }

    private function seedStudies(User $author, User $reviewer): void
    {
        /** @var list<array{theme: string, ustadz: string, day: int, time: string}> $routines */
        $routines = [
            ['theme' => 'Kajian Tafsir Al-Quran', 'ustadz' => 'Ustadz Abdul Rahman, Lc.', 'day' => 1, 'time' => '19:30:00'],
            ['theme' => 'Kajian Fiqih Ibadah Sehari-hari', 'ustadz' => 'Ustadz Hasan Basri, S.Ag.', 'day' => 3, 'time' => '19:30:00'],
            ['theme' => 'Kajian Sirah Nabawiyah', 'ustadz' => 'Ustadz Muhammad Yusuf', 'day' => 5, 'time' => '18:30:00'],
            ['theme' => 'Kajian Muslimah', 'ustadz' => 'Ustadzah Nur Aisyah', 'day' => 6, 'time' => '09:00:00'],
        ];

        foreach ($routines as $routine) {
            Study::factory()->create([
                'theme' => $routine['theme'],
                'ustadz_name' => $routine['ustadz'],
                'schedule_type' => ScheduleType::Rutin,
                'day_of_week' => $routine['day'],
                'time' => $routine['time'],
                'status' => ContentStatus::Disetujui,
                'created_by' => $author->id,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now()->subWeek(),
            ])->attachTags(['Kajian Rutin']);
        }

        $special = Study::factory()->incidental()->withRsvp(80)->create([
            'theme' => 'Tabligh Akbar: Menyambut Ramadhan',
            'ustadz_name' => 'Ustadz Salim Abdullah, Lc., M.A.',
            'time' => '08:00:00',
            'end_time' => '11:00:00',
            'status' => ContentStatus::Disetujui,
            'created_by' => $author->id,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now()->subDays(2),
        ]);

        $special->attachTags(['Ramadhan']);
        Rsvp::factory()->count(12)->create([
            'rsvpable_type' => Study::class,
            'rsvpable_id' => $special->id,
        ]);

        Study::factory()->awaitingApproval()->create([
            'theme' => 'Kelas Tahsin Pemula',
            'ustadz_name' => 'Ustadz Ridwan',
            'day_of_week' => 2,
            'time' => '18:30:00',
            'created_by' => $author->id,
        ]);
    }

    private function seedEvents(User $author, User $reviewer): void
    {
        $baksos = Event::factory()->approved()->withRsvp(100)->create([
            'title' => 'Bakti Sosial dan Santunan Anak Yatim',
            'description' => '<p>Kegiatan santunan untuk 50 anak yatim di sekitar Tangcity Mall, dilanjutkan dengan buka puasa bersama.</p>',
            'event_date' => today()->addDays(12),
            'category' => 'Sosial',
            'created_by' => $author->id,
            'reviewed_by' => $reviewer->id,
        ]);

        $baksos->attachTags(['Sosial', 'Anak & Remaja']);
        Rsvp::factory()->count(8)->create([
            'rsvpable_type' => Event::class,
            'rsvpable_id' => $baksos->id,
        ]);

        Event::factory()->approved()->create([
            'title' => 'Peringatan Maulid Nabi Muhammad SAW',
            'event_date' => today()->addDays(30),
            'category' => 'Hari Besar',
            'created_by' => $author->id,
            'reviewed_by' => $reviewer->id,
        ]);

        Event::factory()->approved()->past()->count(4)->create([
            'created_by' => $author->id,
            'reviewed_by' => $reviewer->id,
        ]);
    }

    private function seedFinance(User $bendahara): void
    {
        $incomeCategories = FinanceCategory::query()->where('type', TransactionType::In)->get();
        $expenseCategories = FinanceCategory::query()->where('type', TransactionType::Out)->get();

        foreach (range(11, 0) as $monthsAgo) {
            $month = today()->subMonths($monthsAgo)->startOfMonth();

            foreach ($incomeCategories as $category) {
                FinanceTransaction::query()->create([
                    'date' => $month->copy()->addDays(random_int(0, 25)),
                    'type' => TransactionType::In,
                    'finance_category_id' => $category->id,
                    'amount' => random_int(500, 9000) * 1000,
                    'description' => $category->name.' bulan '.$month->translatedFormat('F Y'),
                    'created_by' => $bendahara->id,
                ]);
            }

            foreach ($expenseCategories as $category) {
                FinanceTransaction::query()->create([
                    'date' => $month->copy()->addDays(random_int(0, 25)),
                    'type' => TransactionType::Out,
                    'finance_category_id' => $category->id,
                    'amount' => random_int(200, 4000) * 1000,
                    'description' => $category->name.' bulan '.$month->translatedFormat('F Y'),
                    'created_by' => $bendahara->id,
                ]);
            }
        }
    }

    private function seedArticles(User $author, User $reviewer): void
    {
        $categories = ArticleCategory::query()->pluck('id')->all();

        Article::factory()->approved()->create([
            'title' => 'Keutamaan Sholat Berjamaah di Masjid',
            'excerpt' => 'Sholat berjamaah memiliki keutamaan dua puluh tujuh derajat dibanding sholat sendirian.',
            'content' => '<p>Rasulullah SAW bersabda bahwa sholat berjamaah lebih utama dua puluh tujuh derajat dibanding sholat sendirian. Keutamaan ini menjadi pengingat bagi kita yang bekerja di area mall untuk tetap menyempatkan diri sholat berjamaah di masjid.</p>',
            'article_category_id' => $categories[0] ?? null,
            'created_by' => $author->id,
            'reviewed_by' => $reviewer->id,
        ])->attachTags(['Fiqih']);

        Article::factory()->approved()->count(5)->create([
            'article_category_id' => fake()->randomElement($categories),
            'created_by' => $author->id,
            'reviewed_by' => $reviewer->id,
        ]);

        Article::factory()->awaitingApproval()->create([
            'title' => 'Adab Menuntut Ilmu di Majelis Kajian',
            'article_category_id' => $categories[0] ?? null,
            'created_by' => $author->id,
        ]);
    }

    private function seedGallery(User $author): void
    {
        /** @var list<array{title: string, category: string}> $albums */
        $albums = [
            ['title' => 'Buka Puasa Bersama Ramadhan', 'category' => 'Ramadhan'],
            ['title' => 'Kajian Rutin Malam Senin', 'category' => 'Kajian'],
            ['title' => 'Santunan Anak Yatim', 'category' => 'Sosial'],
        ];

        foreach ($albums as $album) {
            $record = GalleryAlbum::factory()->create([
                'title' => $album['title'],
                'category' => $album['category'],
                'created_by' => $author->id,
            ]);

            GalleryItem::factory()->count(6)->create(['gallery_album_id' => $record->id]);
            $record->attachTags([$album['category']]);
        }
    }

    private function seedLibrary(User $author): void
    {
        $studies = Study::query()->approved()->get();

        foreach ($studies->take(3) as $study) {
            LibraryMaterial::factory()->create([
                'title' => 'Slide '.$study->theme,
                'study_id' => $study->id,
                'ustadz_name' => $study->ustadz_name,
                'created_by' => $author->id,
            ]);

            LibraryMaterial::factory()->video()->create([
                'title' => 'Rekaman '.$study->theme,
                'study_id' => $study->id,
                'ustadz_name' => $study->ustadz_name,
                'created_by' => $author->id,
            ]);
        }
    }

    private function seedBoardMembers(): void
    {
        /** @var list<array{name: string, position: string}> $members */
        $members = [
            ['name' => 'H. Ahmad Fauzi', 'position' => 'Ketua DKM'],
            ['name' => 'H. Sulaiman', 'position' => 'Wakil Ketua DKM'],
            ['name' => 'Budi Santoso', 'position' => 'Sekretaris'],
            ['name' => 'Citra Dewi', 'position' => 'Bendahara'],
            ['name' => 'Ustadz Ridwan', 'position' => 'Koordinator Bidang Dakwah'],
            ['name' => 'Rizky Pratama', 'position' => 'Koordinator Remaja Masjid'],
        ];

        foreach ($members as $index => $member) {
            BoardMember::factory()->create([
                'name' => $member['name'],
                'position' => $member['position'],
                'sort_order' => $index + 1,
            ]);
        }
    }

    private function seedJamaahInteractions(User $reviewer): void
    {
        Testimonial::factory()->approved()->count(6)->create(['moderated_by' => $reviewer->id]);
        Testimonial::factory()->count(3)->create();
        Testimonial::factory()->anonymous()->approved()->create(['moderated_by' => $reviewer->id]);

        Suggestion::factory()->count(4)->create();
        Suggestion::factory()->handled()->count(2)->create(['handled_by' => $reviewer->id]);
        Suggestion::factory()->anonymous()->create();

        QurbanRegistration::factory()->count(5)->create();
        QurbanRegistration::factory()->paid()->count(3)->create(['confirmed_by' => $reviewer->id]);
        ZakatRegistration::factory()->count(4)->create();
        ZakatRegistration::factory()->maal()->paid()->count(2)->create(['confirmed_by' => $reviewer->id]);

        $facility = Facility::query()->firstOrFail();
        FacilityBooking::factory()->create(['facility_id' => $facility->id]);
        FacilityBooking::factory()->approved()->create([
            'facility_id' => $facility->id,
            'booking_date' => today()->addDays(20),
            'reviewed_by' => $reviewer->id,
        ]);
    }
}
