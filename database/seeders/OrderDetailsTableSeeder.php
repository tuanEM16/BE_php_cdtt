<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderDetailsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('order_detail')->insert([
            [
                'order_id' => 1,
                'product_id' => 1,
                'price' => 250000,
                'qty' => 2,
                'amount' => 500000,
                'discount' => 0
            ]
        ]);
    }
}
