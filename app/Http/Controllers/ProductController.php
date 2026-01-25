<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\ProductImage;
use App\Models\ProductAttribute;
use App\Models\ProductSale;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
class ProductController extends Controller
{
    public function product_new(Request $request)
    {
        try {
            $limit = $request->limit ?? 10;
            $products = Product::where('status', 1)
                ->with(['product_images', 'sale'])
                ->withSum('productStores as total_qty', 'qty')
                ->addSelect([
                    'is_active_sale' => ProductSale::selectRaw('COUNT(*)')
                        ->whereColumn('product_id', 'product.id') // Lưu ý: 'product.id' là tên bảng sản phẩm của bạn
                        ->where('date_begin', '<=', now())
                        ->where('date_end', '>=', now())
                        ->where('status', 1)
                ])
                ->orderBy('is_active_sale', 'DESC')
                ->orderBy('id', 'DESC')
                ->get();
            $finalResult = [];
            foreach ($products as $product) {
                $stock = $product->total_qty ?? 0;
                if ($stock <= 0) {
                    continue;
                }
                $product->price_sale = $product->sale ? $product->sale->price_sale : null;
                if ($product->price_sale && $product->price_buy > 0) {
                    $product->discount_percent = round((($product->price_buy - $product->price_sale) / $product->price_buy) * 100);
                } else {
                    $product->discount_percent = 0;
                }
                $finalResult[] = $product;
                if (count($finalResult) >= $limit) {
                    break;
                }
            }
            return response()->json([
                'success' => true,
                'message' => 'Tải danh sách thành công',
                'data' => [
                    'data' => $finalResult
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }
    public function index()
    {
        $products = Product::where('status', '!=', 0)
            ->with(['category', 'sale']) // <--- QUAN TRỌNG: Thêm 'sale' vào đây
            ->withSum('productStores as qty', 'qty')
            ->orderBy('created_at', 'DESC')
            ->paginate(20);
        $products->getCollection()->transform(function ($product) {
            $product->price_sale = $product->sale ? $product->sale->price_sale : null;
            return $product;
        });
        return response()->json([
            'success' => true,
            'message' => 'Tải danh sách thành công',
            'data' => $products
        ], 200);
    }
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
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
            if ($request->hasFile('thumbnail')) {
                $file = $request->file('thumbnail');
                $ext = $file->getClientOriginalExtension();
                $filename = time() . '_thumb.' . $ext;
                $file->move(public_path('images/product'), $filename);
                $product->thumbnail = $filename;
            }
            $product->save();
            if ($request->qty > 0) {
                ProductStore::insert([
                    'product_id' => $product->id,
                    'qty' => $request->qty,
                    'price_root' => $request->price_root ?? 0,
                    'created_at' => now(),
                    'created_by' => 1,
                    'status' => 1
                ]);
            }
            if ($request->hasFile('product_images')) {
                foreach ($request->file('product_images') as $key => $file) {
                    $ext = $file->getClientOriginalExtension();
                    $filename = time() . '_gallery_' . $key . '.' . $ext;
                    $file->move(public_path('images/product'), $filename);
                    ProductImage::insert(['product_id' => $product->id, 'image' => $filename]);
                }
            }
            if ($request->has('attributes_json')) {
                $attributes = json_decode($request->attributes_json, true);
                if (is_array($attributes)) {
                    foreach ($attributes as $attr) {
                        if (!empty($attr['attribute_id']) && !empty($attr['value'])) {
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
    public function show($id)
    {
        $product = Product::with(['category', 'product_images', 'product_attributes.attribute', 'sale'])->find($id);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        }
        $product->qty = ProductStore::where('product_id', $id)->sum('qty');
        $product->price_sale = $product->sale ? $product->sale->price_sale : null; // Gán ra ngoài cho tiện
        return response()->json(['success' => true, 'data' => $product], 200);
    }
    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product)
            return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
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
            if ($request->hasFile('thumbnail')) {
                $oldPath = public_path('images/product/' . $product->thumbnail);
                if ($product->thumbnail && File::exists($oldPath))
                    File::delete($oldPath);
                $file = $request->file('thumbnail');
                $filename = time() . '_thumb_upd.' . $file->getClientOriginalExtension();
                $file->move(public_path('images/product'), $filename);
                $product->thumbnail = $filename;
            }
            $product->save();
            if ($request->filled('qty') && $request->qty > 0) {
                ProductStore::insert(['product_id' => $product->id, 'qty' => $request->qty, 'created_at' => now(), 'status' => 1]);
            }
            if ($request->hasFile('product_images')) {
                foreach ($request->file('product_images') as $key => $file) {
                    $filename = time() . '_gallery_upd_' . $key . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('images/product'), $filename);
                    ProductImage::insert(['product_id' => $product->id, 'image' => $filename]);
                }
            }
            if ($request->has('attributes_json')) {
                ProductAttribute::where('product_id', $id)->delete();
                $attributes = json_decode($request->attributes_json, true);
                if (is_array($attributes)) {
                    foreach ($attributes as $attr) {
                        if (!empty($attr['attribute_id']) && !empty($attr['value'])) {
                            ProductAttribute::insert(['product_id' => $product->id, 'attribute_id' => $attr['attribute_id'], 'value' => $attr['value']]);
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
    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product)
            return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        DB::beginTransaction();
        try {
            if ($product->thumbnail && File::exists(public_path('images/product/' . $product->thumbnail))) {
                File::delete(public_path('images/product/' . $product->thumbnail));
            }
            $galleryImages = ProductImage::where('product_id', $id)->get();
            foreach ($galleryImages as $img) {
                if (File::exists(public_path('images/product/' . $img->image)))
                    File::delete(public_path('images/product/' . $img->image));
            }
            ProductImage::where('product_id', $id)->delete();
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