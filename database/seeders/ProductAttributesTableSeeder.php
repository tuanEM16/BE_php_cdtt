<?php
namespace Database\Seeders;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class ProductAttributesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('product_attribute')->insert([
            [
                'product_id' => 1,
                'attribute_id' => 1,
                'value' => '60x60'
            ],
            [
                'product_id' => 1,
                'attribute_id' => 2,
                'value' => 'Xám'
            ]
        ]);
    }
}
