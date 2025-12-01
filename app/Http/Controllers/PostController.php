<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index()
{
    $posts = Post::where('status', '!=', 0)
        ->where('post_type', 'post') // Chỉ lấy bài viết (tránh lấy trang đơn)
        ->orderBy('created_at', 'DESC')
        ->paginate(10); // Tin tức nên phân trang

    return response()->json(['success' => true, 'message' => 'Tải danh sách bài viết thành công', 'data' => $posts], 200);
}
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
