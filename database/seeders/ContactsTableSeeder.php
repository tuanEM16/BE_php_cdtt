<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContactsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('contact')->insert([
            [
                'user_id' => null,
                'name' => 'Nguyen Van A',
                'email' => 'a@gmail.com',
                'phone' => '0123456789',
                'content' => 'Tôi muốn hỏi sản phẩm.',
                'reply_id' => 0,
                'created_at' => now(),
                'created_by' => 1,
                'status' => 1
            ]
        ]);
    }
}
