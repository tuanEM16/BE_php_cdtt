<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index()
    {
        // Lấy menu, sắp xếp theo vị trí rồi đến thứ tự, sau đó là cấp cha/con
        $menus = Menu::where('status', '!=', 0)
            ->orderBy('position', 'ASC')
            ->orderBy('sort_order', 'ASC')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Tải danh sách menu thành công',
            'data' => $menus
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
