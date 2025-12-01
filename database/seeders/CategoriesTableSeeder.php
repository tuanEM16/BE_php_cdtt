<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str; 

class CategoriesTableSeeder extends Seeder
{
    public function run()
    {
        $categoryNames = [
            'Gạch Lát Nền',
            'Gạch Ốp Tường',
            'Gạch Sân Vườn',
            'Gạch Trang Trí',
            'Gạch Giả Gỗ',
            'Sàn Gỗ Công Nghiệp',
            'Thiết Bị Vệ Sinh',
            'Đèn Trang Trí',
            'Sơn Nội Thất',
            'Phụ Kiện Xây Dựng'
        ];

        $data = [];

        foreach ($categoryNames as $key => $name) {
            $data[] = [
                'name' => $name,
                'slug' => Str::slug($name), 
                'image' => 'cat' . ($key + 1) . '.jpg', 
                'parent_id' => 0,
                'sort_order' => $key + 1,
                'description' => 'Chuyên cung cấp các loại ' . mb_strtolower($name) . ' chất lượng cao.',
                'created_at' => now(),
                'created_by' => 1,
                'status' => 1
            ];
        }

        DB::table('category')->insert($data);
    }
}