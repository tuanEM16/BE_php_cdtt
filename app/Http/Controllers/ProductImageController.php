<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductImage;

class ProductImageController extends Controller
{
    // 1. Lấy danh sách ảnh
    public function index()
    {
        $images = ProductImage::with('product')
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json(['success' => true, 'data' => $images], 200);
    }

    // 2. Upload ảnh mới
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $productImage = new ProductImage();
        $productImage->product_id = $request->product_id;
        $productImage->alt = $request->alt;
        $productImage->title = $request->title;

        // Xử lý upload file
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $ext = $file->getClientOriginalExtension();
            $filename = time() . '.' . $ext;
            
            // Lưu vào thư mục public/images/product
            $file->move(public_path('images/product'), $filename);
            $productImage->image = $filename;
        }

        $productImage->save();

        return response()->json(['success' => true, 'message' => 'Thêm ảnh thành công'], 201);
    }

    // 3. Xóa ảnh
    public function destroy($id)
    {
        $productImage = ProductImage::find($id);
        if (!$productImage) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        }

        // Xóa file ảnh trong thư mục
        $path = public_path('images/product/' . $productImage->image);
        if (file_exists($path)) {
            unlink($path);
        }

        $productImage->delete();
        return response()->json(['success' => true, 'message' => 'Xóa thành công'], 200);
    }
}