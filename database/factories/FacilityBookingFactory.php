<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Facility;
use App\Models\FacilityBooking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FacilityBooking>
 */
class FacilityBookingFactory extends Factory
{
    protected $model = FacilityBooking::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'facility_id' => Facility::factory(),
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'purpose' => fake()->randomElement(['Akad nikah', 'Rapat pengurus', 'Pengajian keluarga', 'Pelatihan remaja masjid']),
            'booking_date' => today()->addWeek(),
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'status' => BookingStatus::Menunggu,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (): array => [
            'status' => BookingStatus::Disetujui,
            'reviewed_by' => User::factory(),
            'reviewed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (): array => [
            'status' => BookingStatus::Ditolak,
            'approval_note' => fake()->sentence(),
            'reviewed_by' => User::factory(),
            'reviewed_at' => now(),
        ]);
    }
}
