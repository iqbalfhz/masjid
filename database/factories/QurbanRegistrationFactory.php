<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Enums\QurbanServiceType;
use App\Models\QurbanRegistration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QurbanRegistration>
 */
class QurbanRegistrationFactory extends Factory
{
    protected $model = QurbanRegistration::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'service_type' => QurbanServiceType::Kurban,
            'animal_type' => fake()->randomElement(array_keys(QurbanRegistration::ANIMAL_TYPES)),
            'quantity' => fake()->numberBetween(1, 3),
            'payment_status' => PaymentStatus::BelumBayar,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (): array => [
            'payment_status' => PaymentStatus::Lunas,
            'confirmed_by' => User::factory(),
            'confirmed_at' => now(),
        ]);
    }

    public function aqiqah(): static
    {
        return $this->state(fn (): array => ['service_type' => QurbanServiceType::Aqiqah]);
    }
}
