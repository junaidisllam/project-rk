<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'alt_phone' => fake()->phoneNumber(),
            'division_id' => 1,
            'division_name' => 'Dhaka',
            'district_id' => 1,
            'district_name' => 'Dhaka',
            'upazila_id' => 1,
            'upazila_name' => 'Savar',
            'union_id' => 1,
            'union_name' => 'Aminbazar',
            'address_details' => fake()->address(),
            'is_default' => false,
        ];
    }
}
