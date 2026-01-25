<?php
namespace Database\Seeders;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class ConfigsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('config')->insert([
            [
                'site_name' => 'TileStore',
                'email' => 'support@tilestore.com',
                'phone' => '0123456789',
                'hotline' => '0987654321',
                'address' => 'Hà Nội',
                'status' => 1
            ]
        ]);
    }
}
