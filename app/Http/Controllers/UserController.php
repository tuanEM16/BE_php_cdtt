<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash; // Quan trọng: Để mã hóa password

class UserController extends Controller
{
    // 1. Lấy danh sách
    public function index()
    {
        $users = User::where('status', '!=', 0)
            ->orderBy('created_at', 'DESC')
            ->paginate(20);

        return response()->json(['success' => true, 'message' => 'Tải danh sách thành công', 'data' => $users], 200);
    }

    // 2. Thêm mới
    public function store(Request $request)
    {
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->username = $request->username;
        // Mã hóa mật khẩu trước khi lưu
        $user->password = Hash::make($request->password);
        $user->roles = $request->roles ?? 'customer';
        $user->status = $request->status ?? 1;
        $user->created_at = now();
        $user->created_by = 1;

        // Upload Avatar
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $ext = $file->getClientOriginalExtension();
            $filename = time() . '.' . $ext;
            $file->move(public_path('images/user'), $filename);
            $user->avatar = $filename;
        }

        $user->save();

        return response()->json(['success' => true, 'message' => 'Thêm thành công', 'data' => $user], 201);
    }

    // 3. Xem chi tiết
    public function show($id)
    {
        $user = User::find($id);
        if (!$user) return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        return response()->json(['success' => true, 'data' => $user], 200);
    }

    // 4. Cập nhật
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->username = $request->username;
        $user->roles = $request->roles;
        $user->status = $request->status;
        $user->updated_at = now();
        $user->updated_by = 1;

        // Nếu người dùng nhập password mới thì đổi, không thì giữ nguyên
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // Upload Avatar mới
        if ($request->hasFile('avatar')) {
            $oldPath = public_path('images/user/' . $user->avatar);
            if ($user->avatar && file_exists($oldPath)) unlink($oldPath);

            $file = $request->file('avatar');
            $ext = $file->getClientOriginalExtension();
            $filename = time() . '.' . $ext;
            $file->move(public_path('images/user'), $filename);
            $user->avatar = $filename;
        }

        $user->save();

        return response()->json(['success' => true, 'message' => 'Cập nhật thành công', 'data' => $user], 200);
    }

    // 5. Xóa
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);

        $path = public_path('images/user/' . $user->avatar);
        if ($user->avatar && file_exists($path)) unlink($path);

        $user->delete();
        return response()->json(['success' => true, 'message' => 'Xóa thành công'], 200);
    }
}