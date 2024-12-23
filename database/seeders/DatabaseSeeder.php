<?php

namespace Database\Seeders;

use App\Models\OrderDetail;
use App\Models\Product;
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
        \App\Models\User::factory(10)->create();
        \App\Models\Category::factory(5)
            ->has(Product::factory()->count(4))
            ->create();

        \App\Models\Order::factory(20)
            ->has(OrderDetail::factory()->count(3))
            ->create();
    }
}
