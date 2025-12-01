<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index()
{
    // Lấy đơn hàng và kèm theo chi tiết sản phẩm bên trong
    $orders = Order::where('status', '!=', 0)
        ->orderBy('created_at', 'DESC')
        ->paginate(20);

    return response()->json(['success' => true, 'message' => 'Tải danh sách đơn hàng thành công', 'data' => $orders], 200);
}
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
