<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
         $this->call([
        BannersTableSeeder::class,
        ContactsTableSeeder::class,
        CategoriesTableSeeder::class,
        ProductsTableSeeder::class,
        ProductImagesTableSeeder::class,
        ProductSalesTableSeeder::class,
        AttributesTableSeeder::class,
        ProductAttributesTableSeeder::class,
        ProductStoresTableSeeder::class,
        OrdersTableSeeder::class,
        OrderDetailsTableSeeder::class,
        PostsTableSeeder::class,
        TopicsTableSeeder::class,
        UsersTableSeeder::class,
        MenusTableSeeder::class,
        ConfigsTableSeeder::class,
    ]);
    }
}
