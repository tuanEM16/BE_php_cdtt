<?php
namespace Database\Seeders;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class AttributesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('attribute')->insert([
            ['name' => 'Kích thước'],
            ['name' => 'Màu sắc'],
            ['name' => 'Chất liệu'],
        ]);
    }
}
