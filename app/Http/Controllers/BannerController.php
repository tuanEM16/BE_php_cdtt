<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banner; // Nhớ import Model
class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::where('status', '!=', 0)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'DESC')
            ->get();
        return response()->json(['success' => true, 'message' => 'Tải dữ liệu thành công', 'data' => $banners], 200);
    }
    public function store(Request $request)
    {
        $banner = new Banner();
        $banner->name = $request->name;
        $banner->link = $request->link;
        $banner->position = $request->position ?? 'slideshow';
        $banner->description = $request->description;
        $banner->sort_order = $request->sort_order ?? 0;
        $banner->status = $request->status ?? 1;
        $banner->created_at = now();
        $banner->created_by = 1;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move(public_path('images/banner'), $filename);
            $banner->image = $filename;
        }
        $banner->save();
        return response()->json(['success' => true, 'message' => 'Thêm thành công', 'data' => $banner], 201);
    }
    public function show($id)
    {
        $banner = Banner::find($id);
        if (!$banner) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        }
        return response()->json(['success' => true, 'data' => $banner], 200);
    }
    public function update(Request $request, $id)
    {
        $banner = Banner::find($id);
        if (!$banner) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        }
        $banner->name = $request->name;
        $banner->link = $request->link;
        $banner->position = $request->position ?? $banner->position;
        $banner->description = $request->description;
        $banner->sort_order = $request->sort_order ?? $banner->sort_order;
        $banner->status = $request->status ?? $banner->status;
        $banner->updated_at = now();
        $banner->updated_by = 1;
        if ($request->hasFile('image')) {
            $oldPath = public_path('images/banner/' . $banner->image);
            if ($banner->image && file_exists($oldPath)) unlink($oldPath);
            $file = $request->file('image');
            $ext = $file->getClientOriginalExtension();
            $filename = time() . '.' . $ext;
            $file->move(public_path('images/banner'), $filename);
            $banner->image = $filename;
        }
        $banner->save();
        return response()->json(['success' => true, 'message' => 'Cập nhật thành công', 'data' => $banner], 200);
    }
    public function destroy($id)
    {
        $banner = Banner::find($id);
        if (!$banner) return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        $path = public_path('images/banner/' . $banner->image);
        if ($banner->image && file_exists($path)) unlink($path);
        $banner->delete();
        return response()->json(['success' => true, 'message' => 'Xóa thành công'], 200);
    }
}