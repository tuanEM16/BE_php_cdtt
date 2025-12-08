<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Topic;
use Illuminate\Support\Str;

class TopicController extends Controller
{
    // 1. Lấy danh sách
    public function index()
    {
        $topics = Topic::where('status', '!=', 0)
            ->orderBy('sort_order', 'ASC')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Tải danh sách chủ đề thành công',
            'data' => $topics
        ], 200);
    }

    // 2. Thêm mới
    public function store(Request $request)
    {
        $topic = new Topic();
        $topic->name = $request->name;
        $topic->slug = Str::slug($request->name);
        $topic->description = $request->description;
        $topic->sort_order = $request->sort_order ?? 0;
        $topic->status = $request->status ?? 1;
        $topic->created_at = now();
        $topic->created_by = 1;

        $topic->save();

        return response()->json(['success' => true, 'message' => 'Thêm thành công', 'data' => $topic], 201);
    }

    // 3. Xem chi tiết
    public function show($id)
    {
        $topic = Topic::find($id);
        if (!$topic) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        }
        return response()->json(['success' => true, 'data' => $topic], 200);
    }

    // 4. Cập nhật
    public function update(Request $request, $id)
    {
        $topic = Topic::find($id);
        if (!$topic) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        }

        $topic->name = $request->name;
        $topic->slug = Str::slug($request->name);
        $topic->description = $request->description;
        $topic->sort_order = $request->sort_order;
        $topic->status = $request->status;
        $topic->updated_at = now();
        $topic->updated_by = 1;

        $topic->save();

        return response()->json(['success' => true, 'message' => 'Cập nhật thành công', 'data' => $topic], 200);
    }

    // 5. Xóa
    public function destroy($id)
    {
        $topic = Topic::find($id);
        if (!$topic) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        }
        $topic->delete();
        return response()->json(['success' => true, 'message' => 'Xóa thành công'], 200);
    }
}