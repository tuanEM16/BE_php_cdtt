<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductSale;

class ProductSaleController extends Controller
{
    // 1. Lấy danh sách
    public function index()
    {
        // Lấy danh sách sale kèm thông tin sản phẩm
        $sales = ProductSale::with('product')
            ->orderBy('date_end', 'DESC')
            ->get();

        return response()->json(['success' => true, 'data' => $sales], 200);
    }

    // 2. Thêm mới
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'price_sale' => 'required|numeric',
            'date_begin' => 'required|date',
            'date_end' => 'required|date|after:date_begin',
        ]);

        $sale = new ProductSale();
        $sale->name = $request->name ?? 'Khuyến mãi'; // Tên chương trình
        $sale->product_id = $request->product_id;
        $sale->price_sale = $request->price_sale;
        $sale->date_begin = $request->date_begin;
        $sale->date_end = $request->date_end;
        $sale->status = $request->status ?? 1;
        $sale->created_at = now();
        $sale->created_by = 1;
        $sale->save();

        return response()->json(['success' => true, 'message' => 'Thêm khuyến mãi thành công', 'data' => $sale], 201);
    }

    // 3. Xem chi tiết (để sửa)
    public function show($id)
    {
        $sale = ProductSale::find($id);
        if (!$sale) return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        return response()->json(['success' => true, 'data' => $sale], 200);
    }

    // 4. Cập nhật
    public function update(Request $request, $id)
    {
        $sale = ProductSale::find($id);
        if (!$sale) return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);

        $sale->name = $request->name;
        $sale->product_id = $request->product_id;
        $sale->price_sale = $request->price_sale;
        $sale->date_begin = $request->date_begin;
        $sale->date_end = $request->date_end;
        $sale->status = $request->status;
        $sale->updated_at = now();
        $sale->updated_by = 1;
        $sale->save();

        return response()->json(['success' => true, 'message' => 'Cập nhật thành công'], 200);
    }

    // 5. Xóa
    public function destroy($id)
    {
        $sale = ProductSale::find($id);
        if (!$sale) return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        $sale->delete();
        return response()->json(['success' => true, 'message' => 'Xóa thành công'], 200);
    }
}