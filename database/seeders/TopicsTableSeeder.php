<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TopicsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('topic')->insert([
            [
                'name' => 'Tin tức',
                'slug' => 'tin-tuc',
                'sort_order' => 1,
                'description' => 'Chuyên mục tin tức',
                'created_at' => now(),
                'created_by' => 1,
                'status' => 1
            ]
        ]);
    }
}
