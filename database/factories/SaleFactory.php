<?php

namespace Database\Factories;

use App\Models\Dealer;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
{
    /**
     * total_amount and payment_status are omitted deliberately — the Sale
     * model derives both on save.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->randomFloat(3, 0.5, 25);
        $rate = fake()->randomFloat(2, 80, 400);

        return [
            'product_id' => Product::factory(),
            'dealer_id' => Dealer::factory(),
            'user_id' => null,
            'customer_name' => fake()->boolean(70) ? fake()->name() : null,
            'quantity' => $quantity,
            'unit_rate' => $rate,
            'amount_paid' => round($quantity * $rate, 2),
            'sale_date' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'notes' => fake()->boolean(20) ? fake()->sentence() : null,
        ];
    }

    public function unpaid(): static
    {
        return $this->state(fn (array $attributes) => ['amount_paid' => 0]);
    }

    public function partiallyPaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount_paid' => round($attributes['quantity'] * $attributes['unit_rate'] * 0.4, 2),
        ]);
    }

    public function on(string $date): static
    {
        return $this->state(fn (array $attributes) => ['sale_date' => $date]);
    }
}
