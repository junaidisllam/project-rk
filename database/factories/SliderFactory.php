<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Slider>
 */
class SliderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'image_path' => $this->faker->imageUrl(),
            'mobile_image_path' => $this->faker->imageUrl(),
            'title' => $this->faker->sentence(),
            'link' => $this->faker->url(),
            'is_active' => $this->faker->boolean(),
            'order_column' => $this->faker->numberBetween(0, 10),
        ];
    }
}
