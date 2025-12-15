<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductSale;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
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
        // Validate dữ liệu
        $request->validate([
            'name' => 'required', // Tên chương trình
            'date_begin' => 'required|date',
            'date_end' => 'required|date|after:date_begin',
            'products' => 'required|array|min:1', // Mảng các sản phẩm được chọn
            'products.*.product_id' => 'required',
            'products.*.price_sale' => 'required|numeric|min:0',
        ], [
            'products.required' => 'Vui lòng chọn ít nhất 1 sản phẩm',
            'date_end.after' => 'Ngày kết thúc phải sau ngày bắt đầu'
        ]);

        DB::beginTransaction(); // Bắt đầu giao dịch
        try {
            $count = 0;
            // Duyệt qua từng sản phẩm được gửi lên từ Frontend
            foreach ($request->products as $item) {
                // Kiểm tra giá gốc (Optional)
                $product = Product::find($item['product_id']);
                if (!$product)
                    continue;

                // Tạo khuyến mãi cho từng sản phẩm
                ProductSale::create([
                    'name' => $request->name,
                    'product_id' => $item['product_id'],
                    'price_sale' => $item['price_sale'], // Giá đã tính từ frontend
                    'date_begin' => $request->date_begin,
                    'date_end' => $request->date_end,
                    'status' => $request->status ?? 1,
                    'created_by' => 1
                ]);
                $count++;
            }

            DB::commit(); // Lưu tất cả
            return response()->json(['success' => true, 'message' => "Đã tạo khuyến mãi cho $count sản phẩm"], 201);

        } catch (\Exception $e) {
            DB::rollBack(); // Có lỗi thì hủy hết
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }    // 3. Xem chi tiết (để sửa)
    public function show($id)
    {
        $sale = ProductSale::find($id);
        if (!$sale)
            return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        return response()->json(['success' => true, 'data' => $sale], 200);
    }

    // 4. Cập nhật
    public function update(Request $request, $id)
    {
        $sale = ProductSale::find($id);
        if (!$sale)
            return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);

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
        if (!$sale)
            return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        $sale->delete();
        return response()->json(['success' => true, 'message' => 'Xóa thành công'], 200);
    }
}