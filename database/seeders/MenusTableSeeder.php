<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenusTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('menu')->insert([
            [
                'name' => 'Trang chủ',
                'link' => '/',
                'type' => 'custom',
                'parent_id' => 0,
                'sort_order' => 1,
                'table_id' => null,
                'position' => 'mainmenu',
                'created_at' => now(),
                'created_by' => 1,
                'status' => 1
            ]
        ]);
    }
}
