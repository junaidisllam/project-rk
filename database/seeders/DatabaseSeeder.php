<?php

namespace Database\Seeders;

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Author::factory()->count(10)->create();
        Category::factory()->count(10)->create();
        Book::factory()->count(50)->create();

        Order::factory()
            ->count(10)
            ->has(OrderItem::factory()->count(3), 'items')
            ->create();
    }
}
