<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Str;
class PostController extends Controller
{
    public function index()
    {
        $posts = Post::where('status', '!=', 0)
            ->where('post_type', 'post') 
            ->orderBy('created_at', 'DESC')
            ->select('id', 'title', 'slug', 'image', 'status', 'created_at', 'post_type', 'content') 
            ->paginate(10); 
        return response()->json(['success' => true, 'message' => 'Tải danh sách thành công', 'data' => $posts], 200);
    }
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required', // Validate trường content
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        $post = new Post();
        $post->title = $request->title;
        $post->slug = Str::slug($request->title);      
        $post->content = $request->input('content');       
        $post->description = $request->description ?? '';
        $post->post_type = 'post'; 
        $post->status = $request->status ?? 1;
        $post->topic_id = $request->topic_id ?? 0;
        $post->created_by = 1; 
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $ext = $file->getClientOriginalExtension();
            $filename = date('YmdHis') . '_' . Str::slug($request->title) . '.' . $ext;
            $file->move(public_path('images/post'), $filename);
            $post->image = $filename;
        }
        $post->save();
        return response()->json(['success' => true, 'message' => 'Thêm thành công', 'data' => $post], 201);
    }
    public function show($id)
    {
        $post = Post::find($id);
        if (!$post) return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        return response()->json(['success' => true, 'data' => $post], 200);
    }
    public function update(Request $request, $id)
    {
        $post = Post::find($id);
        if (!$post) return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        $post->title = $request->title;
        $post->slug = Str::slug($request->title);
        $post->content = $request->input('content');
        $post->description = $request->description ?? $post->description;
        $post->status = $request->status ?? $post->status;
        $post->updated_by = 1;
        if ($request->hasFile('image')) {
            $path = public_path('images/post/' . $post->image);
            if (file_exists($path) && $post->image) unlink($path);
            $file = $request->file('image');
            $ext = $file->getClientOriginalExtension();
            $filename = date('YmdHis') . '_' . Str::slug($request->title) . '.' . $ext;
            $file->move(public_path('images/post'), $filename);
            $post->image = $filename;
        }
        $post->save();
        return response()->json(['success' => true, 'message' => 'Cập nhật thành công', 'data' => $post], 200);
    }
    public function destroy($id)
    {
        $post = Post::find($id);
        if (!$post) return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        $path = public_path('images/post/' . $post->image);
        if (file_exists($path) && $post->image) unlink($path);
        $post->delete();
        return response()->json(['success' => true, 'message' => 'Xóa thành công'], 200);
    }
}