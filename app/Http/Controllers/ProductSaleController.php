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
        // 1. Validate dữ liệu đầu vào cơ bản
        $request->validate([
            'name' => 'required',
            'date_begin' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_begin', // Sửa after thành after_or_equal cho chuẩn
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required',
            'products.*.price_sale' => 'required|numeric|min:0',
        ], [
            'products.required' => 'Vui lòng chọn ít nhất 1 sản phẩm',
            'date_end.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu'
        ]);

        DB::beginTransaction();
        try {
            $count = 0;

            // Duyệt qua từng sản phẩm
            foreach ($request->products as $item) {
                $product = Product::find($item['product_id']);
                if (!$product)
                    continue;

                // --- [START] KIỂM TRA TRÙNG NGÀY KHUYẾN MÃI ---

                // Logic: Tìm xem trong DB đã có bản ghi Sale nào của SP này
                // mà thời gian bị chồng lấn với thời gian đang nhập không?
                $conflict = ProductSale::where('product_id', $item['product_id'])
                    ->where(function ($query) use ($request) {
                        $query->where('date_begin', '<=', $request->date_end)
                            ->where('date_end', '>=', $request->date_begin);
                    })
                    ->first();

                // Nếu tìm thấy => Báo lỗi và Rollback ngay lập tức
                if ($conflict) {
                    throw new \Exception(
                        "Sản phẩm '" . $product->name . "' đang có khuyến mãi trùng đợt này ("
                        . date('d/m/Y', strtotime($conflict->date_begin)) . " - "
                        . date('d/m/Y', strtotime($conflict->date_end)) . ")."
                    );
                }
                // --- [END] KIỂM TRA TRÙNG NGÀY KHUYẾN MÃI ---

                // Nếu không trùng thì tạo mới
                ProductSale::create([
                    'name' => $request->name,
                    'product_id' => $item['product_id'],
                    'price_sale' => $item['price_sale'],
                    'date_begin' => $request->date_begin,
                    'date_end' => $request->date_end,
                    'status' => $request->status ?? 1,
                    'created_by' => 1 // Nên sửa thành Auth::id() nếu có đăng nhập
                ]);
                $count++;
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => "Đã tạo khuyến mãi cho $count sản phẩm"], 201);

        } catch (\Exception $e) {
            DB::rollBack(); // Hủy toàn bộ thao tác nếu có 1 sản phẩm bị lỗi
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422); // Trả về mã 422 (Unprocessable Entity)
        }
    }
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