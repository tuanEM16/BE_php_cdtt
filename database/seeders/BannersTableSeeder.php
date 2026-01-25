<?php
namespace Database\Seeders;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class BannersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
 public function run()
    {
        DB::table('banner')->insert([
            [
                'name' => 'Slide 1',
                'image' => 'slide1.jpg',
                'link' => null,
                'position' => 'slideshow',
                'sort_order' => 1,
                'description' => 'Banner slide',
                'created_at' => now(),
                'created_by' => 1,
                'status' => 1
            ],
        ]);
    }
}
