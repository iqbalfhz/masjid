<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Enums\ZakatType;
use App\Models\User;
use App\Models\ZakatRegistration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ZakatRegistration>
 */
class ZakatRegistrationFactory extends Factory
{
    protected $model = ZakatRegistration::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'zakat_type' => ZakatType::Fitrah,
            'soul_count' => fake()->numberBetween(1, 6),
            'payment_status' => PaymentStatus::BelumBayar,
        ];
    }

    public function maal(): static
    {
        return $this->state(fn (): array => [
            'zakat_type' => ZakatType::Maal,
            'soul_count' => null,
            'amount' => fake()->numberBetween(500, 10000) * 1000,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (): array => [
            'payment_status' => PaymentStatus::Lunas,
            'confirmed_by' => User::factory(),
            'confirmed_at' => now(),
        ]);
    }
}
