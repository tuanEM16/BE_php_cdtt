<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductAttribute;
use App\Models\ProductStore; // Import bảng kho
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    // 1. GET: Lấy danh sách
    public function index()
    {
        $products = Product::where('status', '!=', 0)
            ->with('category')
            ->orderBy('created_at', 'DESC')
            ->withSum('productStores as qty', 'qty')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Tải danh sách thành công',
            'data' => $products
        ], 200);
    }

    // 2. POST: Thêm mới
    public function store(Request $request)
    {
        DB::beginTransaction(); 
        try {
            // A. Lưu bảng Product (KHÔNG CÓ qty)
            $product = new Product();
            $product->name = $request->name;
            $product->slug = Str::slug($request->name);
            $product->category_id = $request->category_id;
            $product->price_buy = $request->price_buy;
            // $product->qty = $request->qty;  <-- ĐÃ XÓA DÒNG GÂY LỖI NÀY
            $product->description = $request->description;
            $product->content = $request->input('content');
            $product->status = $request->status ?? 1;
            $product->created_at = now();
            $product->created_by = 1;

            if ($request->hasFile('thumbnail')) {
                $file = $request->file('thumbnail');
                $ext = $file->getClientOriginalExtension();
                $filename = time() . '_thumb.' . $ext;
                $file->move(public_path('images/product'), $filename);
                $product->thumbnail = $filename;
            }

            $product->save(); // Lưu xong có ID

            // B. Lưu vào kho (ProductStore) - Đây mới là chỗ lưu qty
            if ($request->qty > 0) {
                ProductStore::insert([
                    'product_id' => $product->id,
                    'qty' => $request->qty, // Số lượng nhập
                    'price_root' => $request->price_root ?? 0, // Giá vốn
                    'created_at' => now(),
                    'created_by' => 1,
                    'status' => 1
                ]);
            }

            // C. Lưu Ảnh Gallery
            if ($request->hasFile('product_images')) {
                foreach ($request->file('product_images') as $key => $file) {
                    $ext = $file->getClientOriginalExtension();
                    $filename = time() . '_gallery_' . $key . '.' . $ext;
                    $file->move(public_path('images/product'), $filename);
                    
                    ProductImage::insert([
                        'product_id' => $product->id,
                        'image' => $filename,
                        'alt' => $product->name,
                        'title' => $product->name
                    ]);
                }
            }

            // D. Lưu Thuộc tính
            if ($request->has('attributes_json')) {
                $attributes = json_decode($request->attributes_json, true);
                if (is_array($attributes)) {
                    foreach ($attributes as $attr) {
                        if(!empty($attr['attribute_id']) && !empty($attr['value'])) {
                            ProductAttribute::insert([
                                'product_id' => $product->id,
                                'attribute_id' => $attr['attribute_id'],
                                'value' => $attr['value']
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Thêm thành công', 'data' => $product], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    // 3. GET: Chi tiết
// File: app/Http/Controllers/ProductController.php

public function show($id)
{
    // Lấy sản phẩm kèm các quan hệ
    $product = Product::with(['category', 'product_images', 'product_attributes.attribute'])->find($id);

    if (!$product) {
        return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
    }

    // --- TÍNH TOÁN TỒN KHO ---
    // Cách 1: Nếu bạn muốn lấy tổng số lượng ĐÃ NHẬP (từ bảng product_store)
    $importedQty = \App\Models\ProductStore::where('product_id', $id)->sum('qty');
    
    // (Nâng cao): Nếu muốn tính TỒN KHO THỰC TẾ = Tổng nhập - Tổng bán
    // $soldQty = \App\Models\OrderDetail::where('product_id', $id)->sum('qty');
    // $currentStock = $importedQty - $soldQty;

    // Gán vào biến qty để trả về Frontend (Frontend đang dùng biến này)
    $product->qty = $importedQty; 
    // -------------------------

    return response()->json(['success' => true, 'data' => $product], 200);
}
    // 4. PUT: Cập nhật
    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);

        DB::beginTransaction();
        try {
            $product->name = $request->name;
            $product->slug = Str::slug($request->name);
            $product->category_id = $request->category_id;
            $product->price_buy = $request->price_buy;
            $product->description = $request->description;
            $product->content = $request->input('content');
            $product->status = $request->status;
            $product->updated_at = now();
            $product->updated_by = 1;

            if ($request->hasFile('thumbnail')) {
                $oldPath = public_path('images/product/' . $product->thumbnail);
                if ($product->thumbnail && File::exists($oldPath)) File::delete($oldPath);

                $file = $request->file('thumbnail');
                $ext = $file->getClientOriginalExtension();
                $filename = time() . '_thumb_upd.' . $ext;
                $file->move(public_path('images/product'), $filename);
                $product->thumbnail = $filename;
            }

            $product->save();

            if ($request->filled('qty') && $request->qty > 0) {
                 ProductStore::insert([
                    'product_id' => $product->id,
                    'qty' => $request->qty,
                    'price_root' => $request->price_root ?? 0,
                    'created_at' => now(),
                    'created_by' => 1,
                    'status' => 1
                ]);
            }

            // Xử lý Gallery (thêm ảnh mới)
            if ($request->hasFile('product_images')) {
                foreach ($request->file('product_images') as $key => $file) {
                    $ext = $file->getClientOriginalExtension();
                    $filename = time() . '_gallery_upd_' . $key . '.' . $ext;
                    $file->move(public_path('images/product'), $filename);
                    
                    ProductImage::insert([
                        'product_id' => $product->id,
                        'image' => $filename,
                        'alt' => $product->name,
                        'title' => $product->name
                    ]);
                }
            }

            // Xử lý Thuộc tính (Reset và thêm lại)
            if ($request->has('attributes_json')) {
                ProductAttribute::where('product_id', $id)->delete();
                $attributes = json_decode($request->attributes_json, true);
                if (is_array($attributes)) {
                    foreach ($attributes as $attr) {
                        if(!empty($attr['attribute_id']) && !empty($attr['value'])) {
                            ProductAttribute::insert([
                                'product_id' => $product->id,
                                'attribute_id' => $attr['attribute_id'],
                                'value' => $attr['value']
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Cập nhật thành công'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    // 5. DELETE: Xóa
    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product) return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);

        DB::beginTransaction();
        try {
            // Xóa ảnh thumbnail
            $thumbPath = public_path('images/product/' . $product->thumbnail);
            if ($product->thumbnail && File::exists($thumbPath)) File::delete($thumbPath);

            // Xóa ảnh gallery
            $galleryImages = ProductImage::where('product_id', $id)->get();
            foreach ($galleryImages as $img) {
                $galleryPath = public_path('images/product/' . $img->image);
                if (File::exists($galleryPath)) File::delete($galleryPath);
            }
            ProductImage::where('product_id', $id)->delete();

            // Xóa thuộc tính và kho
            ProductAttribute::where('product_id', $id)->delete();
            ProductStore::where('product_id', $id)->delete();

            $product->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Xóa thành công'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Lỗi xóa: ' . $e->getMessage()], 500);
        }
    }
}