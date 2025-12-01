<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Topic;
class TopicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index()
    {
        $topics = Topic::where('status', '!=', 0)
            ->orderBy('sort_order', 'ASC') // Sắp xếp theo thứ tự ưu tiên
            ->select('id', 'name', 'slug', 'sort_order', 'status')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Tải danh sách chủ đề thành công',
            'data' => $topics
        ], 200);
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
