<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str; 

class ProductsTableSeeder extends Seeder
{
    public function run()
    {
        $products = [];

        for ($i = 1; $i <= 20; $i++) {
            $products[] = [
                'category_id' => rand(1, 5), 
                'name' => 'Gạch 60x60 Mẫu số ' . $i,
                'slug' => 'gach-60x60-mau-so-' . $i,
                'thumbnail' => 'p' . rand(1, 5) . '.jpg',
                'content' => 'Nội dung chi tiết cho sản phẩm gạch mẫu số ' . $i . '. Gạch chất lượng cao...',
                'description' => 'Gạch lát nền 60x60 cao cấp mẫu ' . $i,
                'price_buy' => rand(150, 500) * 1000, 
                'created_at' => now(),
                'created_by' => 1,
                'status' => 1
            ];
        }

        DB::table('product')->insert($products);
    }
}