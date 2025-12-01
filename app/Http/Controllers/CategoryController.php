<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::where('status', '!=', 0)
            ->orderBy('sort_order', 'ASC') 
            ->get(); 

        return response()->json([
            'success' => true,
            'message' => 'Tải danh sách danh mục thành công',
            'data' => $categories
        ], 200);
    }

    // ... Các hàm store, show, update, destroy bạn có thể viết tương tự ProductController


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
