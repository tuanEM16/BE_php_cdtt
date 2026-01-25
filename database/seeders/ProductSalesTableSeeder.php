<?php
namespace Database\Seeders;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class ProductSalesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('product_sale')->insert([
            [
                'name' => 'Khuyến mãi đầu năm',
                'product_id' => 1,
                'price_sale' => 200000,
                'date_begin' => now(),
                'date_end' => now()->addDays(30),
                'created_at' => now(),
                'created_by' => 1,
                'status' => 1
            ]
        ]);
    }
}
