<?php
namespace Database\Seeders;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class OrdersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('order')->insert([
            [
                'user_id' => 1,
                'name' => 'Nguyen Van A',
                'email' => 'a@gmail.com',
                'phone' => '0123456789',
                'address' => 'Hà Nội',
                'note' => null,
                'created_at' => now(),
                'created_by' => 1,
                'status' => 1
            ]
        ]);
    }
}
