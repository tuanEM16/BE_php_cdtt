<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail;

class OrderController extends Controller
{
    // 1. Lấy danh sách (Kèm chi tiết để tính tổng tiền ở Frontend)
    public function index()
    {
        $orders = Order::where('status', '!=', 0)
            ->with('orderdetails') // Eager loading lấy chi tiết
            ->orderBy('created_at', 'DESC')
            ->get();

        return response()->json(['success' => true, 'data' => $orders], 200);
    }

    // 2. Xem chi tiết đơn hàng (Kèm thông tin sản phẩm trong đơn)
    public function show($id)
    {
        // Lấy đơn hàng, kèm chi tiết, trong chi tiết kèm thông tin Product
        $order = Order::with('orderdetails.product')->find($id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        }
        return response()->json(['success' => true, 'data' => $order], 200);
    }

    // 3. Cập nhật trạng thái đơn hàng
    public function update(Request $request, $id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        }

        // Quy ước status: 1: Chờ xác nhận, 2: Đang giao, 3: Đã giao, 4: Hủy
        $order->status = $request->status ?? $order->status;
        $order->updated_at = now();
        $order->updated_by = 1;
        $order->save();

        return response()->json(['success' => true, 'message' => 'Cập nhật trạng thái thành công'], 200);
    }

    // 4. Xóa đơn hàng (Cẩn thận: Xóa luôn chi tiết)
    public function destroy($id)
    {
        $order = Order::find($id);
        if (!$order) return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);

        // Xóa các dòng trong bảng chi tiết trước
        OrderDetail::where('order_id', $id)->delete();
        $order->delete();

        return response()->json(['success' => true, 'message' => 'Xóa đơn hàng thành công'], 200);
    }
}