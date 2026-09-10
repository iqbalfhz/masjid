<?php

namespace Database\Seeders;

use App\Enums\TransactionType;
use App\Models\ArticleCategory;
use App\Models\Facility;
use App\Models\Faq;
use App\Models\FinanceCategory;
use App\Models\MosqueSetting;
use Illuminate\Database\Seeder;

/**
 * Master data yang dibutuhkan sistem agar langsung bisa dipakai:
 * pengaturan masjid, kategori keuangan & artikel, fasilitas, dan FAQ awal.
 */
class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedMosqueSetting();
        $this->seedFinanceCategories();
        $this->seedArticleCategories();
        $this->seedFacilities();
        $this->seedFaqs();
    }

    private function seedMosqueSetting(): void
    {
        MosqueSetting::query()->updateOrCreate(['id' => 1], [
            'name' => 'Masjid An-Nur',
            'tagline' => 'Masjid Tangcity Mall — melayani jamaah karyawan, pengunjung, dan masyarakat sekitar',
            'address' => 'Lantai P3a, Tangcity Mall, Kota Tangerang',
            'phone' => '021-0000000',
            'email' => 'info@masjidannur.test',
            'description' => 'Masjid An-Nur berada di lingkungan Tangcity Mall dan melayani jamaah karyawan, pengunjung mall, serta masyarakat sekitar.',
            'history' => 'Masjid An-Nur didirikan untuk memenuhi kebutuhan ibadah karyawan dan pengunjung Tangcity Mall, dan kini berkembang menjadi pusat kegiatan keagamaan bagi masyarakat sekitar.',
            'vision' => 'Menjadi masjid yang makmur, informatif, dan transparan bagi seluruh jamaah.',
            'mission' => "Menyelenggarakan ibadah dan kajian rutin yang berkualitas.\nMengelola keuangan masjid secara transparan dan akuntabel.\nMenjadi pusat kegiatan sosial dan pendidikan Islam bagi jamaah.",
            'bank_name' => 'Bank Syariah Indonesia',
            'bank_account_name' => 'DKM Masjid An-Nur',
            'bank_account_number' => '7000000001',
            'latitude' => config('masjid.prayer.latitude'),
            'longitude' => config('masjid.prayer.longitude'),
            'prayer_calculation_method' => (string) config('masjid.prayer.method'),
            'prayer_reminder_settings' => [
                'enabled' => true,
                'prayers' => ['fajr', 'dhuhr', 'asr', 'maghrib', 'isha'],
                'minutes_before' => 10,
            ],
        ]);
    }

    private function seedFinanceCategories(): void
    {
        /** @var list<array{name: string, type: TransactionType, description: string}> $categories */
        $categories = [
            ['name' => 'Infaq Jumat', 'type' => TransactionType::In, 'description' => 'Kotak infaq sholat Jumat'],
            ['name' => 'Infaq Harian', 'type' => TransactionType::In, 'description' => 'Kotak infaq harian jamaah'],
            ['name' => 'Donasi Perorangan', 'type' => TransactionType::In, 'description' => 'Donasi transfer/tunai dari jamaah'],
            ['name' => 'Donasi Perusahaan', 'type' => TransactionType::In, 'description' => 'Donasi dari tenant dan perusahaan'],
            ['name' => 'Zakat', 'type' => TransactionType::In, 'description' => 'Penerimaan zakat fitrah dan maal'],
            ['name' => 'Listrik & Air', 'type' => TransactionType::Out, 'description' => 'Tagihan utilitas masjid'],
            ['name' => 'Kebersihan', 'type' => TransactionType::Out, 'description' => 'Perlengkapan dan jasa kebersihan'],
            ['name' => 'Honor Ustadz', 'type' => TransactionType::Out, 'description' => 'Honor pengisi kajian'],
            ['name' => 'Konsumsi Kegiatan', 'type' => TransactionType::Out, 'description' => 'Konsumsi kajian dan kegiatan'],
            ['name' => 'Pemeliharaan Sarana', 'type' => TransactionType::Out, 'description' => 'Perbaikan dan perawatan fasilitas'],
            ['name' => 'Santunan Sosial', 'type' => TransactionType::Out, 'description' => 'Bantuan sosial untuk jamaah dan masyarakat'],
        ];

        foreach ($categories as $category) {
            FinanceCategory::query()->updateOrCreate(
                ['slug' => str($category['name'])->slug()->value()],
                [
                    'name' => $category['name'],
                    'type' => $category['type'],
                    'description' => $category['description'],
                    'is_active' => true,
                ],
            );
        }
    }

    private function seedArticleCategories(): void
    {
        foreach (['Kajian Islam', 'Info Masjid', 'Fiqih Ibadah', 'Kisah Teladan', 'Ramadhan'] as $name) {
            ArticleCategory::query()->updateOrCreate(
                ['slug' => str($name)->slug()->value()],
                ['name' => $name],
            );
        }
    }

    private function seedFacilities(): void
    {
        /** @var list<array{name: string, capacity: int, description: string}> $facilities */
        $facilities = [
            ['name' => 'Ruang Utama Masjid', 'capacity' => 300, 'description' => 'Ruang sholat utama, dapat dipakai untuk pengajian besar dan akad nikah.'],
            ['name' => 'Aula Serbaguna', 'capacity' => 80, 'description' => 'Ruang serbaguna untuk rapat, pelatihan, dan kegiatan remaja masjid.'],
            ['name' => 'Ruang Kelas TPA', 'capacity' => 30, 'description' => 'Ruang belajar untuk TPA dan kelas tahsin.'],
            ['name' => 'Ruang Sekretariat DKM', 'capacity' => 15, 'description' => 'Ruang rapat pengurus DKM.'],
        ];

        foreach ($facilities as $facility) {
            Facility::query()->updateOrCreate(
                ['slug' => str($facility['name'])->slug()->value()],
                [
                    'name' => $facility['name'],
                    'capacity' => $facility['capacity'],
                    'description' => $facility['description'],
                    'is_active' => true,
                ],
            );
        }
    }

    private function seedFaqs(): void
    {
        /** @var list<array{question: string, answer: string, category: string}> $faqs */
        $faqs = [
            ['question' => 'Di mana lokasi persis Masjid An-Nur?', 'answer' => 'Masjid An-Nur berada di Lantai P3a, Tangcity Mall, Kota Tangerang.', 'category' => 'Umum'],
            ['question' => 'Jam berapa masjid dibuka?', 'answer' => 'Masjid dibuka mulai sebelum waktu Subuh hingga setelah Isya, mengikuti jam operasional mall.', 'category' => 'Umum'],
            ['question' => 'Bagaimana cara berdonasi ke masjid?', 'answer' => 'Donasi dapat disalurkan melalui kotak infaq di masjid, transfer ke rekening resmi DKM, atau scan QRIS yang tertera pada halaman Donasi.', 'category' => 'Donasi'],
            ['question' => 'Apakah laporan keuangan masjid bisa dilihat jamaah?', 'answer' => 'Bisa. Seluruh pemasukan dan pengeluaran dipublikasikan pada halaman Laporan Keuangan dan dapat difilter per periode maupun kategori.', 'category' => 'Donasi'],
            ['question' => 'Bagaimana cara meminjam fasilitas masjid?', 'answer' => 'Ajukan lewat halaman Peminjaman Fasilitas dengan memilih fasilitas, tanggal, dan jam yang tersedia. Pengurus akan meninjau dan mengabarkan hasilnya.', 'category' => 'Fasilitas'],
            ['question' => 'Apakah perlu mendaftar untuk mengikuti kajian?', 'answer' => 'Sebagian besar kajian terbuka untuk umum tanpa pendaftaran. Untuk kajian atau kegiatan tertentu, tersedia form konfirmasi kehadiran (RSVP) agar panitia dapat menyiapkan tempat dan konsumsi.', 'category' => 'Kajian'],
            ['question' => 'Di mana saya bisa mendapatkan materi kajian yang sudah lewat?', 'answer' => 'Materi kajian berupa slide, rekaman audio, maupun video diarsipkan pada halaman E-Library dan dapat diakses kapan saja.', 'category' => 'Kajian'],
            ['question' => 'Bagaimana cara menyampaikan saran atau keluhan?', 'answer' => 'Gunakan halaman Kotak Saran. Masukan dapat dikirim secara anonim, namun mengisi kontak akan memudahkan pengurus menyampaikan tindak lanjut.', 'category' => 'Umum'],
        ];

        foreach ($faqs as $index => $faq) {
            Faq::query()->updateOrCreate(
                ['question' => $faq['question']],
                [
                    'answer' => $faq['answer'],
                    'category' => $faq['category'],
                    'sort_order' => $index + 1,
                    'is_published' => true,
                ],
            );
        }
    }
}
