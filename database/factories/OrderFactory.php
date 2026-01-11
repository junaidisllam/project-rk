<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'invoice_no' => 'INV-'.$this->faker->unique()->numberBetween(1000, 9999),
            'name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'alt_phone' => $this->faker->optional()->phoneNumber(),
            'division_id' => $this->faker->optional()->randomNumber(),
            'division_name' => $this->faker->city(),
            'district_id' => $this->faker->optional()->randomNumber(),
            'district_name' => $this->faker->city(),
            'upazila_id' => $this->faker->optional()->randomNumber(),
            'upazila_name' => $this->faker->city(),
            'union_id' => $this->faker->optional()->randomNumber(),
            'union_name' => $this->faker->city(),
            'address_details' => $this->faker->address(),
            'subtotal' => $this->faker->randomFloat(2, 100, 1000),
            'shipping_charge' => $this->faker->randomFloat(2, 5, 50),
            'total_price' => function (array $attributes) {
                return $attributes['subtotal'] + $attributes['shipping_charge'];
            },
            'payment_method' => $this->faker->randomElement(['cod', 'bkash', 'sslcommerz']),
            'payment_status' => $this->faker->randomElement(['pending', 'paid']),
            'status' => $this->faker->randomElement(['pending', 'processing', 'shipped', 'delivered', 'cancelled']),
            'note' => $this->faker->optional()->sentence(),
        ];
    }
}
