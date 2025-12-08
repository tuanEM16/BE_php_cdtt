<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductStore;
use App\Models\Product; // Import Model Product để cập nhật tồn kho

class ProductStoreController extends Controller
{
    // 1. Lấy danh sách lịch sử nhập
    public function index()
    {
        $stores = ProductStore::with('product')
            ->orderBy('created_at', 'DESC')
            ->get();

        return response()->json(['success' => true, 'data' => $stores], 200);
    }

    // 2. Nhập hàng mới
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'price_root' => 'required|numeric',
            'qty' => 'required|numeric|min:1',
        ]);

        // A. Lưu vào lịch sử nhập kho (bảng product_store)
        $store = new ProductStore();
        $store->product_id = $request->product_id;
        $store->price_root = $request->price_root;
        $store->qty = $request->qty;
        $store->status = 1;
        $store->created_at = now();
        $store->created_by = 1;
        $store->save();

        // B. Cộng dồn số lượng vào bảng sản phẩm chính (bảng product)
        $product = Product::find($request->product_id);
        if ($product) {
            $product->qty = $product->qty + $request->qty; // Cộng thêm số lượng vừa nhập
            
            // Cập nhật lại giá nhập (nếu cần) hoặc chỉ cập nhật số lượng
            // $product->price_buy = ... (Thường giá bán không đổi theo giá nhập ngay)
            
            $product->updated_at = now();
            $product->save();
        }

        return response()->json(['success' => true, 'message' => 'Nhập kho thành công', 'data' => $store], 201);
    }

    // 3. Xóa lịch sử nhập (Cẩn thận: Xóa lịch sử có nên trừ lại kho không? Thường là KHÔNG hoặc cấm xóa)
    public function destroy($id)
    {
        $store = ProductStore::find($id);
        if (!$store) return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        
        // Ở đây tôi chỉ xóa lịch sử, không trừ lại kho (tùy nghiệp vụ của bạn)
        $store->delete();
        return response()->json(['success' => true, 'message' => 'Xóa lịch sử thành công'], 200);
    }
}