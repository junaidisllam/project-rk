<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->sentence(3);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'writer_id' => Author::factory(),
            'description' => $this->faker->paragraph,
            'price' => $this->faker->randomFloat(2, 10, 100),
            'original_price' => $this->faker->optional()->randomFloat(2, 10, 100),
            'cover_photo' => $this->faker->imageUrl(),
            'category_id' => Category::factory(),
            'summary' => $this->faker->optional()->paragraph,
            'specification' => null,
            'book_preview' => null,
            'published_date' => $this->faker->optional()->date(),
            'stock_quantity' => $this->faker->numberBetween(0, 100),
            'is_active' => $this->faker->boolean,
        ];
    }
}
