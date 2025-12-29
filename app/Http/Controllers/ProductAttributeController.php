<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductAttribute;

class ProductAttributeController extends Controller
{
    // 1. Lấy danh sách
    public function index()
    {
        $items = ProductAttribute::with(['product', 'attribute'])
            ->orderBy('id', 'DESC')
            ->get();
            
        return response()->json(['success' => true, 'data' => $items], 200);
    }

    // 2. Thêm mới
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'attribute_id' => 'required',
            'value' => 'required',
        ]);

        $item = new ProductAttribute();
        $item->product_id = $request->product_id;
        $item->attribute_id = $request->attribute_id;
        $item->value = $request->value;
        $item->save();

        return response()->json(['success' => true, 'message' => 'Thêm thành công', 'data' => $item], 201);
    }

    // 3. Lấy chi tiết (Dùng cho trang Edit) -- MỚI THÊM
    public function show($id)
    {
        $item = ProductAttribute::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy dữ liệu'], 404);
        }
        return response()->json(['success' => true, 'data' => $item], 200);
    }

    // 4. Cập nhật -- MỚI THÊM
    public function update(Request $request, $id)
    {
        $item = ProductAttribute::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy dữ liệu'], 404);
        }

        $request->validate([
            'product_id' => 'required',
            'attribute_id' => 'required',
            'value' => 'required',
        ]);

        $item->product_id = $request->product_id;
        $item->attribute_id = $request->attribute_id;
        $item->value = $request->value;
        $item->save();

        return response()->json(['success' => true, 'message' => 'Cập nhật thành công', 'data' => $item], 200);
    }

    // 5. Xóa
    public function destroy($id)
    {
        $item = ProductAttribute::find($id);
        if ($item) $item->delete();
        return response()->json(['success' => true, 'message' => 'Xóa thành công'], 200);
    }
}