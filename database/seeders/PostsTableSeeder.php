<?php
namespace Database\Seeders;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class PostsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('post')->insert([
            [
                'topic_id' => 1,
                'title' => 'Tin tức vật liệu xây dựng',
                'slug' => 'tin-tuc-vat-lieu',
                'image' => 'post1.jpg',
                'content' => 'Nội dung bài viết...',
                'description' => 'Tin tức VLXD',
                'post_type' => 'post',
                'created_at' => now(),
                'created_by' => 1,
                'status' => 1
            ]
        ]);
    }
}
