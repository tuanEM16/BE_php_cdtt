<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str;
class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::where('status', '!=', 0)
            ->orderBy('sort_order', 'ASC')
            ->get(); 
        return response()->json([
            'success' => true,
            'message' => 'Tải danh sách danh mục thành công',
            'data' => $categories
        ], 200);
    }
    /**
     * 2. POST: Thêm danh mục mới
     */
    public function store(Request $request)
    {
        $category = new Category();
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $category->parent_id = $request->parent_id ?? 0;
        $category->sort_order = $request->sort_order ?? 0;
        $category->description = $request->description;
        $category->status = $request->status ?? 1;
        $category->created_at = now();
        $category->created_by = 1;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move(public_path('images/category'), $filename);
            $category->image = $filename;
        }
        $category->save();
        return response()->json([
            'success' => true,
            'message' => 'Thêm danh mục thành công',
            'data' => $category
        ], 201);
    }
    /**
     * 3. GET: Xem chi tiết danh mục
     */
    public function show($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy danh mục'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'Tải dữ liệu thành công',
            'data' => $category
        ], 200);
    }
    /**
     * 4. PUT/POST: Cập nhật danh mục
     */
    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy danh mục'
            ], 404);
        }
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $category->parent_id = $request->parent_id ?? $category->parent_id;
        $category->sort_order = $request->sort_order ?? $category->sort_order;
        $category->description = $request->description;
        $category->status = $request->status ?? $category->status;
        $category->updated_at = now();
        $category->updated_by = 1;
        if ($request->hasFile('image')) {
            $oldImagePath = public_path('images/category/' . $category->image);
            if ($category->image && file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move(public_path('images/category'), $filename);
            $category->image = $filename;
        }
        $category->save();
        return response()->json([
            'success' => true,
            'message' => 'Cập nhật danh mục thành công',
            'data' => $category
        ], 200);
    }
    /**
     * 5. DELETE: Xóa danh mục và ảnh
     */
    public function destroy($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy danh mục'
            ], 404);
        }
        $imagePath = public_path('images/category/' . $category->image);
        if ($category->image && file_exists($imagePath)) {
            unlink($imagePath);
        }
        $category->delete();
        return response()->json([
            'success' => true,
            'message' => 'Xóa danh mục thành công'
        ], 200);
    }
}