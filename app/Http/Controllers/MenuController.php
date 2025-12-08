<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;

class MenuController extends Controller
{
    // 1. Lấy danh sách
    public function index()
    {
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

    // 2. Thêm mới
    public function store(Request $request)
    {
        $menu = new Menu();
        $menu->name = $request->name;
        $menu->link = $request->link;
        $menu->type = $request->type ?? 'custom';
        $menu->parent_id = $request->parent_id ?? 0;
        $menu->sort_order = $request->sort_order ?? 0;
        $menu->position = $request->position ?? 'mainmenu';
        $menu->status = $request->status ?? 1;
        $menu->created_at = now();
        $menu->created_by = 1;

        $menu->save();

        return response()->json(['success' => true, 'message' => 'Thêm thành công', 'data' => $menu], 201);
    }

    // 3. Xem chi tiết
    public function show($id)
    {
        $menu = Menu::find($id);
        if (!$menu) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        }
        return response()->json(['success' => true, 'data' => $menu], 200);
    }

    // 4. Cập nhật
    public function update(Request $request, $id)
    {
        $menu = Menu::find($id);
        if (!$menu) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        }

        $menu->name = $request->name;
        $menu->link = $request->link;
        $menu->type = $request->type;
        $menu->parent_id = $request->parent_id;
        $menu->sort_order = $request->sort_order;
        $menu->position = $request->position;
        $menu->status = $request->status;
        $menu->updated_at = now();
        $menu->updated_by = 1;

        $menu->save();

        return response()->json(['success' => true, 'message' => 'Cập nhật thành công', 'data' => $menu], 200);
    }

    // 5. Xóa
    public function destroy($id)
    {
        $menu = Menu::find($id);
        if (!$menu) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        }
        $menu->delete();
        return response()->json(['success' => true, 'message' => 'Xóa thành công'], 200);
    }
}