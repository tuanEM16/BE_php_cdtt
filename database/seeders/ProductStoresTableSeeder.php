<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductStoresTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('product_store')->insert([
            [
                'product_id' => 1,
                'price_root' => 200000,
                'qty' => 500,
                'created_at' => now(),
                'created_by' => 1,
                'status' => 1
            ]
        ]);
    }
}
