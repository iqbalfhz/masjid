<?php

use App\Enums\BookingStatus;
use App\Enums\ModerationStatus;
use App\Enums\PaymentStatus;
use App\Enums\SuggestionStatus;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Facility;
use App\Models\FacilityBooking;
use App\Models\QurbanRegistration;
use App\Models\Rsvp;
use App\Models\Study;
use App\Models\Suggestion;
use App\Models\Testimonial;
use App\Models\ZakatRegistration;

beforeEach(function (): void {
    seedMasterData();
    seedRoles();
});

/**
 * Payload honeypot yang meniru form yang diisi manusia: kolom umpan kosong dan
 * waktu render sudah cukup lama.
 */
function honeypotFields(): array
{
    return [
        config('masjid.forms.honeypot_field') => '',
        config('masjid.forms.honeypot_time_field') => encrypt(now()->subMinute()->timestamp),
    ];
}

it('menyimpan testimoni baru dengan status menunggu moderasi', function (): void {
    $this->post('/testimoni', [
        ...honeypotFields(),
        'name' => 'Hamba Allah',
        'message' => 'Masjidnya bersih dan nyaman untuk sholat di sela jam kerja.',
    ])->assertRedirect()->assertSessionHas('status');

    $testimonial = Testimonial::query()->firstOrFail();

    expect($testimonial->status)->toBe(ModerationStatus::Menunggu)
        ->and($testimonial->name)->toBe('Hamba Allah');
});

it('menerima testimoni anonim', function (): void {
    $this->post('/testimoni', [
        ...honeypotFields(),
        'message' => 'Terima kasih pengurus, kajiannya sangat bermanfaat sekali.',
    ])->assertSessionHasNoErrors();

    expect(Testimonial::query()->firstOrFail()->displayName())->toBe('Jamaah');
});

it('menolak testimoni yang mengisi kolom honeypot', function (): void {
    $this->post('/testimoni', [
        ...honeypotFields(),
        config('masjid.forms.honeypot_field') => 'https://spam.example.com',
        'message' => 'Pesan dari bot yang mengisi semua kolom form.',
    ])->assertSessionHasErrors('form');

    expect(Testimonial::query()->count())->toBe(0);
});

it('menolak form yang dikirim terlalu cepat setelah dimuat', function (): void {
    $this->post('/testimoni', [
        config('masjid.forms.honeypot_field') => '',
        config('masjid.forms.honeypot_time_field') => encrypt(now()->timestamp),
        'message' => 'Pesan yang dikirim seketika oleh skrip otomatis.',
    ])->assertSessionHasErrors('form');

    expect(Testimonial::query()->count())->toBe(0);
});

it('menolak testimoni yang terlalu pendek', function (): void {
    $this->post('/testimoni', [...honeypotFields(), 'message' => 'Bagus'])
        ->assertSessionHasErrors('message');
});

it('menyimpan masukan jamaah beserta nomor tiketnya', function (): void {
    $this->post('/kotak-saran', [
        ...honeypotFields(),
        'name' => 'Budi',
        'contact' => '08123456789',
        'category' => 'fasilitas',
        'message' => 'Mohon tambahan kipas angin di area shaf belakang.',
    ])->assertSessionHas('reference');

    $suggestion = Suggestion::query()->firstOrFail();

    expect($suggestion->status)->toBe(SuggestionStatus::Baru)
        ->and($suggestion->ticket_code)->toStartWith('SR-');
});

it('menerima masukan anonim tanpa kontak', function (): void {
    $this->post('/kotak-saran', [
        ...honeypotFields(),
        'category' => 'lainnya',
        'message' => 'Sarannya agar jadwal kajian juga ditempel di papan pengumuman.',
    ])->assertSessionHasNoErrors();

    expect(Suggestion::query()->firstOrFail()->isAnonymous())->toBeTrue();
});

it('mengirim notifikasi internal ke pengurus saat ada masukan baru', function (): void {
    $sekretaris = userWithRole(UserRole::Sekretaris);

    $this->post('/kotak-saran', [
        ...honeypotFields(),
        'category' => 'kegiatan',
        'message' => 'Usul agar kajian remaja diadakan tiap akhir pekan.',
    ])->assertSessionHasNoErrors();

    expect($sekretaris->fresh()->notifications()->count())->toBe(1);
});

it('mencatat konfirmasi kehadiran untuk kajian yang membuka RSVP', function (): void {
    $study = Study::factory()->approved()->withRsvp()->create();

    $this->post('/rsvp', [
        ...honeypotFields(),
        'jenis' => 'kajian',
        'id' => $study->id,
        'name' => 'Ahmad',
        'phone' => '08123456789',
        'person_count' => 3,
    ])->assertSessionHasNoErrors();

    expect($study->rsvps()->sum('person_count'))->toBe(3);
});

it('menolak RSVP untuk kajian yang tidak membuka konfirmasi kehadiran', function (): void {
    $study = Study::factory()->approved()->create(['rsvp_enabled' => false]);

    $this->post('/rsvp', [
        ...honeypotFields(),
        'jenis' => 'kajian',
        'id' => $study->id,
        'name' => 'Ahmad',
        'phone' => '08123456789',
        'person_count' => 1,
    ])->assertNotFound();
});

it('menolak RSVP ketika kuota peserta sudah penuh', function (): void {
    $event = Event::factory()->approved()->withRsvp(5)->create();
    Rsvp::factory()->create([
        'rsvpable_type' => Event::class,
        'rsvpable_id' => $event->id,
        'person_count' => 5,
    ]);

    $this->post('/rsvp', [
        ...honeypotFields(),
        'jenis' => 'kegiatan',
        'id' => $event->id,
        'name' => 'Siti',
        'phone' => '08123456789',
        'person_count' => 1,
    ])->assertSessionHasErrors('person_count');

    expect($event->rsvps()->sum('person_count'))->toBe(5);
});

it('mendaftarkan kurban dan memberi nomor pendaftaran', function (): void {
    $this->post('/layanan/kurban', [
        ...honeypotFields(),
        'name' => 'Pak Hasan',
        'phone' => '08123456789',
        'service_type' => 'kurban',
        'animal_type' => 'kambing',
        'quantity' => 2,
    ])->assertSessionHas('reference');

    $registration = QurbanRegistration::query()->firstOrFail();

    expect($registration->payment_status)->toBe(PaymentStatus::BelumBayar)
        ->and($registration->registration_number)->toStartWith('QRB-');
});

it('mendaftarkan zakat fitrah dengan jumlah jiwa', function (): void {
    $this->post('/layanan/zakat', [
        ...honeypotFields(),
        'name' => 'Bu Aminah',
        'phone' => '08123456789',
        'zakat_type' => 'fitrah',
        'soul_count' => 4,
    ])->assertSessionHas('reference');

    expect(ZakatRegistration::query()->firstOrFail()->soul_count)->toBe(4);
});

it('mewajibkan nominal untuk zakat maal', function (): void {
    $this->post('/layanan/zakat', [
        ...honeypotFields(),
        'name' => 'Pak Umar',
        'phone' => '08123456789',
        'zakat_type' => 'maal',
    ])->assertSessionHasErrors('amount');
});

it('mengirim notifikasi ke bendahara saat ada pendaftaran layanan baru', function (): void {
    $bendahara = userWithRole(UserRole::Bendahara);

    $this->post('/layanan/kurban', [
        ...honeypotFields(),
        'name' => 'Pak Hasan',
        'phone' => '08123456789',
        'service_type' => 'aqiqah',
        'animal_type' => 'kambing',
        'quantity' => 1,
    ])->assertSessionHasNoErrors();

    expect($bendahara->fresh()->notifications()->count())->toBe(1);
});

it('menyimpan pengajuan peminjaman fasilitas dengan status menunggu', function (): void {
    $facility = Facility::query()->firstOrFail();

    $this->post('/peminjaman-fasilitas', [
        ...honeypotFields(),
        'facility_id' => $facility->id,
        'name' => 'Panitia Akad',
        'phone' => '08123456789',
        'purpose' => 'Akad nikah',
        'booking_date' => today()->addWeek()->toDateString(),
        'start_time' => '09:00',
        'end_time' => '11:00',
    ])->assertSessionHas('reference');

    $booking = FacilityBooking::query()->firstOrFail();

    expect($booking->status)->toBe(BookingStatus::Menunggu)
        ->and($booking->booking_number)->toStartWith('PJF-');
});

it('menolak pengajuan fasilitas yang bentrok dengan jadwal terpakai', function (): void {
    $facility = Facility::query()->firstOrFail();

    FacilityBooking::factory()->approved()->create([
        'facility_id' => $facility->id,
        'booking_date' => today()->addWeek(),
        'start_time' => '09:00:00',
        'end_time' => '12:00:00',
    ]);

    $this->post('/peminjaman-fasilitas', [
        ...honeypotFields(),
        'facility_id' => $facility->id,
        'name' => 'Pemohon Kedua',
        'phone' => '08123456789',
        'purpose' => 'Rapat pengurus',
        'booking_date' => today()->addWeek()->toDateString(),
        'start_time' => '10:00',
        'end_time' => '13:00',
    ])->assertSessionHasErrors('booking_date');

    expect(FacilityBooking::query()->count())->toBe(1);
});

it('mengizinkan pengajuan fasilitas pada jam yang tidak bersinggungan', function (): void {
    $facility = Facility::query()->firstOrFail();

    FacilityBooking::factory()->approved()->create([
        'facility_id' => $facility->id,
        'booking_date' => today()->addWeek(),
        'start_time' => '09:00:00',
        'end_time' => '11:00:00',
    ]);

    $this->post('/peminjaman-fasilitas', [
        ...honeypotFields(),
        'facility_id' => $facility->id,
        'name' => 'Pemohon Kedua',
        'phone' => '08123456789',
        'purpose' => 'Rapat pengurus',
        'booking_date' => today()->addWeek()->toDateString(),
        'start_time' => '11:00',
        'end_time' => '13:00',
    ])->assertSessionHasNoErrors();

    expect(FacilityBooking::query()->count())->toBe(2);
});

it('menolak pengajuan fasilitas untuk tanggal yang sudah lewat', function (): void {
    $this->post('/peminjaman-fasilitas', [
        ...honeypotFields(),
        'facility_id' => Facility::query()->firstOrFail()->id,
        'name' => 'Pemohon',
        'phone' => '08123456789',
        'purpose' => 'Rapat',
        'booking_date' => today()->subDay()->toDateString(),
        'start_time' => '09:00',
        'end_time' => '11:00',
    ])->assertSessionHasErrors('booking_date');
});
