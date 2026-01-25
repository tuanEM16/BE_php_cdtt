<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductStore;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail; // ✅ Thư viện Mail
use App\Mail\OrderSuccessEmail;      // ✅ Class Mail
use Illuminate\Support\Facades\Log;  // ✅ Log để kiểm tra lỗi
class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('status', '!=', 0)
            ->with('orderdetails')
            ->orderBy('created_at', 'DESC')
            ->get();
        return response()->json(['success' => true, 'data' => $orders], 200);
    }
    public function show($id)
    {
        $order = Order::with('orderdetails.product')->find($id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        }
        return response()->json(['success' => true, 'data' => $order], 200);
    }
    public function createOrder(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Token không hợp lệ'
            ], 401);
        }
        DB::beginTransaction();
        try {
            $order = new Order();
            $order->user_id = $user->id;
            $order->name = $request->name;
            $order->phone = $request->phone;
            $order->email = $request->email;
            $order->address = $request->address;
            $order->note = $request->note;
            $order->status = 1; // ⏳ CHỜ THANH TOÁN
            $order->save();
            foreach ($request->cart as $item) {
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'price' => $item['price'],
                    'qty' => $item['qty'],
                    'discount' => $item['discount'] ?? 0,
                    'amount' => ($item['price'] - ($item['discount'] ?? 0)) * $item['qty'],
                ]);
            }
            DB::commit();
            return response()->json([
                'success' => true,
                'order_id' => $order->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function updateShippingById(Request $request, $id)
    {
        $user = $request->user();
        $data = $request->validate([
            'address' => 'required|string|max:255',
        ]);
        $order = \DB::table('lcte_order')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->whereIn('status', [1, 2]) // chỉ cho sửa khi chưa hoàn tất/hủy
            ->first();
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hợp lệ'], 404);
        }
        \DB::table('lcte_order')
            ->where('id', $id)
            ->update([
                'address' => $data['address'],
                'updated_at' => now(),
                'updated_by' => $user->id,
            ]);
        return response()->json(['success' => true]);
    }
    public function updateAddress(Request $request, Order $order)
    {
        $user = $request->user();
        if ($order->user_id != $user->id) {
            return response()->json(['success' => false, 'message' => 'Không có quyền'], 403);
        }
        if ((int) $order->status !== 1) {
            return response()->json(['success' => false, 'message' => 'Chỉ sửa khi đơn đang chờ thanh toán'], 422);
        }
        $data = $request->validate([
            'address' => 'required|string|max:255',
        ]);
        $order->address = $data['address'];
        $order->save();
        return response()->json(['success' => true, 'data' => $order]);
    }
    public function cancel(Request $request, Order $order)
    {
        $user = $request->user();
        if ($order->user_id != $user->id) {
            return response()->json(['success' => false, 'message' => 'Không có quyền'], 403);
        }
        if ((int) $order->status !== 1) {
            return response()->json(['success' => false, 'message' => 'Chỉ hủy khi đơn đang chờ thanh toán'], 422);
        }
        $order->status = 4; // đã hủy
        $order->save();
        return response()->json(['success' => true, 'data' => $order]);
    }
    public function store(Request $request)
    {
        $request->validate(['cart' => 'required|array']);
        $user = auth()->user();
        DB::beginTransaction();
        try {
            $pendingOrder = null;
            if ($user) {
                $pendingOrder = Order::where('user_id', $user->id)->where('status', 1)->first();
            }
            if ($pendingOrder) {
                $order = $pendingOrder;
                OrderDetail::where('order_id', $order->id)->delete();
            } else {
                $order = new Order();
                $order->created_at = now();
            }
            if ($user) {
                $order->user_id = $user->id;
                $order->name = $user->name;
                $order->email = $user->email;
                $order->phone = $user->phone ?? $request->phone ?? '';
                $order->address = $user->address ?? $request->address ?? '';
            } else {
                $order->user_id = 1;
                $order->name = $request->name ?? 'Khách lẻ';
                $order->email = $request->email ?? '';
                $order->phone = $request->phone ?? '';
                $order->address = $request->address ?? '';
            }
            $order->note = $request->note;
            $order->status = 1; // Chờ thanh toán
            $order->updated_at = now();
            $order->save();
            foreach ($request->cart as $item) {
                if (!isset($item['id']) || !isset($item['qty']))
                    continue;
                $detail = new OrderDetail();
                $detail->order_id = $order->id;
                $detail->product_id = $item['id'];
                $detail->price = $item['price'] ?? 0;
                $detail->qty = $item['qty'];
                $detail->amount = ($item['price'] ?? 0) * $item['qty'];
                $detail->discount = $item['discount'] ?? 0;
                $detail->save();
            }
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Tạo đơn thành công',
                'order_id' => $order->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    public function getHistory(Request $request)
    {
        $user = auth()->user();
        if (!$user)
            return response()->json(['success' => false, 'message' => 'Chưa đăng nhập'], 401);
        $orders = Order::where('user_id', $user->id)
            ->with('orderdetails.product')
            ->orderBy('created_at', 'DESC')
            ->get();
        return response()->json(['success' => true, 'data' => $orders]);
    }
    public function update(Request $request, $id)
    {
        $order = Order::with('orderdetails.product')->find($id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hàng'], 404);
        }
        $oldStatus = $order->status; // Trạng thái cũ
        $newStatus = $request->status ?? $order->status; // Trạng thái mới nhận được
        $order->status = $newStatus;
        $order->updated_at = now();
        $order->save();
        if ($oldStatus != 2 && $newStatus == 2) {
            try {
                if ($order->email) {
                    Mail::to($order->email)->send(new OrderSuccessEmail($order));
                    Log::info("Đã gửi mail xác nhận cho đơn hàng #{$order->id}");
                }
            } catch (\Exception $e) {
                Log::error("Gửi mail thất bại đơn #{$order->id}: " . $e->getMessage());
            }
        }
        return response()->json(['success' => true, 'message' => 'Cập nhật thành công'], 200);
    }
    public function destroy($id)
    {
        $order = Order::find($id);
        if (!$order)
            return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        OrderDetail::where('order_id', $id)->delete();
        $order->delete();
        return response()->json(['success' => true, 'message' => 'Xóa thành công'], 200);
    }
}
