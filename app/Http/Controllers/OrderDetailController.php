<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrderDetail;

class OrderDetailController extends Controller
{
    // Lấy danh sách tất cả sản phẩm đã bán (Kèm thông tin Đơn hàng và Sản phẩm)
    public function index()
    {
        $details = OrderDetail::with(['order', 'product'])
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json(['success' => true, 'data' => $details], 200);
    }

    // Xem chi tiết 1 dòng (ít dùng)
    public function show($id)
    {
        $detail = OrderDetail::find($id);
        if (!$detail) return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        return response()->json(['success' => true, 'data' => $detail], 200);
    }

    // Xóa dòng chi tiết (Chỉ dùng khi cần sửa lỗi dữ liệu)
    public function destroy($id)
    {
        $detail = OrderDetail::find($id);
        if ($detail) $detail->delete();
        return response()->json(['success' => true, 'message' => 'Xóa thành công'], 200);
    }
}