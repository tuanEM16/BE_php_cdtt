<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductStore;
class ProductStoreController extends Controller
{
    public function index()
    {
        $stores = ProductStore::with('product')
            ->orderBy('created_at', 'DESC')
            ->get();
        return response()->json(['success' => true, 'data' => $stores], 200);
    }
    public function show($id)
    {
        $store = ProductStore::with('product.category')->find($id);
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy phiếu nhập'], 404);
        }
        return response()->json(['success' => true, 'data' => $store], 200);
    }
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'price_root' => 'required|numeric',
            'qty' => 'required|numeric|min:1',
        ]);
        $store = new ProductStore();
        $store->product_id = $request->product_id;
        $store->price_root = $request->price_root;
        $store->qty = $request->qty;
        $store->status = 1;
        $store->created_at = now();
        $store->created_by = 1; // Tạm set cứng admin ID = 1
        $store->save();
        return response()->json(['success' => true, 'message' => 'Nhập kho thành công', 'data' => $store], 201);
    }
    public function update(Request $request, $id)
    {
        $store = ProductStore::find($id);
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy phiếu nhập'], 404);
        }
        $store->price_root = $request->price_root;
        $store->qty = $request->qty;
        $store->updated_by = 1;
        $store->updated_at = now();
        $store->save();
        return response()->json(['success' => true, 'message' => 'Cập nhật thành công'], 200);
    }
    public function destroy($id)
    {
        $store = ProductStore::find($id);
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy phiếu nhập'], 404);
        }
        $store->delete();
        return response()->json(['success' => true, 'message' => 'Xóa thành công'], 200);
    }
}