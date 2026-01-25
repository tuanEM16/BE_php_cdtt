<?php
namespace Database\Seeders;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class ProductImagesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('product_image')->insert([
            [
                'product_id' => 1,
                'image' => 'p1_1.jpg',
                'alt' => 'Hình 1',
                'title' => 'Gạch 60x60'
            ]
        ]);
    }
}
