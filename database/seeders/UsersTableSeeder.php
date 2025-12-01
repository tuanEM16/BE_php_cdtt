<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class UsersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('user')->insert([
            [
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'phone' => '0123456789',
                'username' => 'admin',
                'password' => bcrypt('123456'),
                'roles' => 'admin',
                'avatar' => null,
                'created_at' => now(),
                'created_by' => 1,
                'status' => 1
            ]
        ]);
    }
}
