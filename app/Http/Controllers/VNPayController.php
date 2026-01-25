<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Mail\OrderSuccessEmail;
class VNPayController extends Controller
{
    private function buildHashData(array $params): string
    {
        ksort($params);
        $hashData = '';
        $i = 0;
        foreach ($params as $key => $value) {
            if ($value === null || $value === '')
                continue;
            $piece = urlencode($key) . "=" . urlencode((string) $value);
            $hashData = ($i === 1) ? ($hashData . '&' . $piece) : $piece;
            $i = 1;
        }
        return $hashData;
    }
    private function sign(array $params): string
    {
        $hashData = $this->buildHashData($params);
        return hash_hmac('sha512', $hashData, env('VNP_HASH_SECRET'));
    }
    private function verifySignature(array $vnpParams, string $secureHash): bool
    {
        unset($vnpParams['vnp_SecureHash'], $vnpParams['vnp_SecureHashType']);
        $calculated = $this->sign($vnpParams);
        return strcasecmp($calculated, $secureHash) === 0;
    }
    public function createPaymentUrl(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer'
        ]);
        $order = Order::with('orderdetails')->findOrFail($request->order_id);
        if ((int) $order->status !== 1) {
            return response()->json(['success' => false, 'message' => 'Đơn hàng không ở trạng thái chờ thanh toán'], 400);
        }
        $total = $order->orderdetails->sum(function ($d) {
            if (!is_null($d->amount))
                return (float) $d->amount;
            $price = (float) ($d->price ?? 0);
            $qty = (int) ($d->qty ?? 0);
            $discount = (float) ($d->discount ?? 0);
            return max($price - $discount, 0) * $qty;
        });
        $vnpUrl = env('VNP_URL');
        $tmnCode = env('VNP_TMN_CODE');
        $returnUrl = env('VNP_RETURN_URL');
        $params = [
            "vnp_Version" => "2.1.0",
            "vnp_Command" => "pay",
            "vnp_TmnCode" => $tmnCode,
            "vnp_Amount" => (int) round($total) * 100, // VNPay x100 :contentReference[oaicite:4]{index=4}
            "vnp_CurrCode" => "VND",
            "vnp_TxnRef" => (string) $order->id,
            "vnp_OrderInfo" => "Thanh toan don hang #" . $order->id, // yêu cầu không dấu, không ký tự đặc biệt :contentReference[oaicite:5]{index=5}
            "vnp_OrderType" => "other",
            "vnp_Locale" => "vn",
            "vnp_ReturnUrl" => $returnUrl,
            "vnp_IpAddr" => $request->ip(), // bắt buộc :contentReference[oaicite:6]{index=6}
            "vnp_CreateDate" => now('Asia/Ho_Chi_Minh')->format('YmdHis'),
        ];
        $secureHash = $this->sign($params);
        $query = '';
        foreach (collect($params)->sortKeys() as $k => $v) {
            $query .= urlencode($k) . "=" . urlencode((string) $v) . '&';
        }
        $paymentUrl = $vnpUrl . "?" . $query . "vnp_SecureHash=" . $secureHash;
        return response()->json([
            'success' => true,
            'payment_url' => $paymentUrl,
            'order_id' => $order->id
        ]);
    }
    public function handleReturn(Request $request)
    {
        try {
            $vnp = [];
            foreach ($request->query() as $k => $v) {
                if (str_starts_with($k, 'vnp_'))
                    $vnp[$k] = $v;
            }
            $orderId = $vnp['vnp_TxnRef'] ?? null;
            $secureHash = $vnp['vnp_SecureHash'] ?? '';
            if (!$orderId) {
                return response()->json(['success' => false, 'message' => 'Thiếu vnp_TxnRef (mã đơn).'], 400);
            }
            if (!$this->verifySignature($vnp, $secureHash)) {
                return response()->json(['success' => false, 'message' => 'Sai chữ ký (SecureHash).'], 400);
            }
            $order = Order::with('orderdetails.product')->find($orderId);
            if (!$order) {
                return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hàng.'], 404);
            }
            $vnpAmount = ((int) ($vnp['vnp_Amount'] ?? 0)) / 100;
            $dbTotal = $order->orderdetails->sum(function ($d) {
                if (!is_null($d->amount))
                    return (float) $d->amount;
                $price = (float) ($d->price ?? 0);
                $qty = (int) ($d->qty ?? 0);
                $discount = (float) ($d->discount ?? 0);
                return max($price - $discount, 0) * $qty;
            });
            if ((int) round($dbTotal) !== (int) round($vnpAmount)) {
                return response()->json(['success' => false, 'message' => 'Sai số tiền.'], 400);
            }
            $respCode = $vnp['vnp_ResponseCode'] ?? '';
            $tranStatus = $vnp['vnp_TransactionStatus'] ?? '';
            if ((int) $order->status === 2) {
                return response()->json(['success' => true, 'paid' => true, 'order_id' => $order->id]);
            }
            if ($respCode === '00' && $tranStatus === '00') {
                $order->status = 2;
                $order->updated_at = now();
                $order->save();
                try {
                    if ($order->email) {
                        Mail::to($order->email)->send(new OrderSuccessEmail($order->load('orderdetails.product')));
                    }
                } catch (\Throwable $e) {
                    Log::error("Send mail failed order #{$order->id}: " . $e->getMessage());
                }
                return response()->json(['success' => true, 'paid' => true, 'order_id' => $order->id]);
            }
            $order->status = 3;
            $order->updated_at = now();
            $order->save();
            return response()->json(['success' => true, 'paid' => false, 'order_id' => $order->id]);
        } catch (\Throwable $e) {
            Log::error("VNPAY RETURN error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }
    public function ipn(Request $request)
    {
        $inputData = [];
        foreach ($request->query() as $key => $value) {
            if (str_starts_with($key, 'vnp_'))
                $inputData[$key] = $value;
        }
        $secureHash = $inputData['vnp_SecureHash'] ?? '';
        $orderId = $inputData['vnp_TxnRef'] ?? null;
        $returnData = [];
        try {
            if (!$orderId) {
                $returnData = ['RspCode' => '01', 'Message' => 'Order not found'];
                return response()->json($returnData);
            }
            if (!$this->verifySignature($inputData, $secureHash)) {
                $returnData = ['RspCode' => '97', 'Message' => 'Invalid signature'];
                return response()->json($returnData);
            }
            $order = Order::with('orderdetails')->find($orderId);
            if (!$order) {
                $returnData = ['RspCode' => '01', 'Message' => 'Order not found'];
                return response()->json($returnData);
            }
            $vnpAmount = ((int) ($inputData['vnp_Amount'] ?? 0)) / 100;
            $dbTotal = $order->orderdetails->sum(function ($d) {
                if (!is_null($d->amount))
                    return (float) $d->amount;
                $price = (float) ($d->price ?? 0);
                $qty = (int) ($d->qty ?? 0);
                $discount = (float) ($d->discount ?? 0);
                return max($price - $discount, 0) * $qty;
            });
            if ((int) round($dbTotal) !== (int) round($vnpAmount)) {
                $returnData = ['RspCode' => '04', 'Message' => 'invalid amount'];
                return response()->json($returnData);
            }
            if ((int) $order->status === 2) {
                $returnData = ['RspCode' => '02', 'Message' => 'Order already confirmed'];
                return response()->json($returnData);
            }
            $respCode = $inputData['vnp_ResponseCode'] ?? '';
            $tranStatus = $inputData['vnp_TransactionStatus'] ?? '';
            if ($respCode === '00' && $tranStatus === '00') {
                $order->status = 2;
                $order->updated_at = now();
                $order->save();
                if ($order->email) {
                    Mail::to($order->email)->send(new OrderSuccessEmail($order->load('orderdetails.product')));
                }
                $returnData = ['RspCode' => '00', 'Message' => 'Confirm Success'];
                return response()->json($returnData);
            }
            $order->status = 3; // tuỳ bạn: 3 = thất bại
            $order->updated_at = now();
            $order->save();
            $returnData = ['RspCode' => '00', 'Message' => 'Confirm Success']; // vẫn confirm đã nhận IPN
            return response()->json($returnData);
        } catch (\Throwable $e) {
            Log::error("VNPAY IPN error: " . $e->getMessage());
            $returnData = ['RspCode' => '99', 'Message' => 'Unknow error'];
            return response()->json($returnData);
        }
    }
}
