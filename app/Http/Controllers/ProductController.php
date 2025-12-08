<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Str; 

class ProductController extends Controller
{
    /**
     * 1. GET
     */
    public function index()
    {
        $products = Product::where('status', '!=', 0)
            ->orderBy('created_at', 'DESC')
            ->paginate(20); 

        return response()->json([
            'success' => true,
            'message' => 'Tải danh sách thành công',
            'data' => $products
        ], 200);
    }

    /**
     * 2. POST: Thêm mới
     */
    public function store(Request $request)
    {
        $product = new Product();
        $product->name = $request->name;
        $product->slug = Str::slug($request->name); 
        $product->category_id = $request->category_id;
        $product->price_buy = $request->price_buy;
        $product->description = $request->description;
        $product->content = $request->input('content'); 
        $product->status = $request->status ?? 1;
        $product->created_at = now();
        $product->created_by = 1; 

        // Upload ảnh
        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move(public_path('images/product'), $filename);
            $product->thumbnail = $filename;
        }

        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Thêm thành công',
            'data' => $product
        ], 201);
    }

    /**
     * 3. GET: Xem chi tiết
     */
    public function show($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        }
        return response()->json(['success' => true, 'data' => $product], 200);
    }

    /**
     * 4. PUT: Cập nhật
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        }

        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->category_id = $request->category_id;
        $product->price_buy = $request->price_buy;
        $product->description = $request->description;
        $product->content = $request->input('content');
        $product->status = $request->status ?? $product->status;
        $product->updated_at = now();
        $product->updated_by = 1;

        // Xử lý ảnh mới
        if ($request->hasFile('thumbnail')) {
            $oldImagePath = public_path('images/product/' . $product->thumbnail);
            if ($product->thumbnail && file_exists($oldImagePath)) {
                unlink($oldImagePath); 
            }
            // ----------------------------------------

            $file = $request->file('thumbnail');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move(public_path('images/product'), $filename);
            $product->thumbnail = $filename;
        }

        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thành công',
            'data' => $product
        ], 200);
    }

    /**
     * 5. DELETE: Xóa sản phẩm và XÓA LUÔN ẢNH
     */
    public function destroy($id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy sản phẩm'], 404);
        }
        $imagePath = public_path('images/product/' . $product->thumbnail);

        if ($product->thumbnail && file_exists($imagePath)) {
            unlink($imagePath);
        }
        // -----------------------------

        $product->delete(); 

        return response()->json([
            'success' => true,
            'message' => 'Xóa sản phẩm và hình ảnh thành công'
        ], 200);
    }
}