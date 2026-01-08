<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; // Để check unique email khi update

class UserController extends Controller
{
    // --- AUTHENTICATION ---

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Lỗi nhập liệu', 'errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Tài khoản hoặc mật khẩu không đúng'], 401);
        }

        if ($user->status == 0) {
            return response()->json(['success' => false, 'message' => 'Tài khoản đã bị khóa'], 403);
        }

        // Tạo token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công',
            'data' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }

    public function register(Request $request)
    {
        // Validation chặt chẽ hơn
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:user', // Lưu ý tên bảng là user
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Lỗi nhập liệu', 'errors' => $validator->errors()], 422);
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        // Tách lấy username từ email nếu không nhập
        $user->username = $request->username ?? explode('@', $request->email)[0]; 
        $user->password = Hash::make($request->password);
        $user->roles = 'customer'; // Mặc định đăng ký là khách hàng
        $user->status = 1;
        $user->created_at = now();
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Đăng ký thành công',
            'data' => $user,
            'access_token' => $token,
        ], 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'message' => 'Đăng xuất thành công']);
    }

    public function profile(Request $request)
    {
        return response()->json(['success' => true, 'data' => $request->user()]);
    }

    // --- CRUD (Dành cho Admin - Cần bảo vệ bằng Route Middleware) ---

    public function index(Request $request) 
    {
        // Không cần check role ở đây nữa nếu đã dùng Middleware ở route
        // Lấy tất cả user trừ những người bị xóa mềm (nếu có dùng softDelete)
        $users = User::orderBy('created_at', 'DESC')->paginate(20);

        return response()->json(['success' => true, 'message' => 'Tải danh sách thành công', 'data' => $users], 200);
    }

    public function store(Request $request)
    {
        // 1. Validate dữ liệu
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:user',
            'password' => 'required|min:6',
            'roles' => 'required|in:admin,customer', // Chỉ cho phép các role hợp lệ
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' // Validate ảnh
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->username = $request->username ?? explode('@', $request->email)[0];
        $user->password = Hash::make($request->password);
        $user->roles = $request->roles;
        $user->status = $request->status ?? 1;
        $user->created_at = now();
        // Lấy ID người đang đăng nhập thực hiện hành động này
        $user->created_by = Auth::id() ?? 1; 

        // Upload Avatar (Sửa lại tên cột thống nhất là image)
        if ($request->hasFile('image')) { // Frontend gửi lên field tên là 'image'
            $file = $request->file('image');
            $ext = $file->getClientOriginalExtension();
            $filename = time() . '.' . $ext;
            $file->move(public_path('images/user'), $filename);
            $user->image = $filename; // Lưu vào cột image
        }

        $user->save();

        return response()->json(['success' => true, 'message' => 'Thêm thành công', 'data' => $user], 201);
    }

    public function show($id)
    {
        $user = User::find($id);
        if (!$user)
            return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        return response()->json(['success' => true, 'data' => $user], 200);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user)
            return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);

        // Validate Update (Chú ý: email unique ngoại trừ chính user này)
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', Rule::unique('user')->ignore($user->id)],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        if($request->username) $user->username = $request->username;
        if($request->roles) $user->roles = $request->roles;
        if($request->status) $user->status = $request->status;
        
        $user->updated_at = now();
        $user->updated_by = Auth::id() ?? 1;

        // Đổi password nếu có nhập
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // Upload Avatar mới
        if ($request->hasFile('image')) {
            // Xóa ảnh cũ
            $oldPath = public_path('images/user/' . $user->image);
            if ($user->image && file_exists($oldPath)) {
                unlink($oldPath);
            }

            $file = $request->file('image');
            $ext = $file->getClientOriginalExtension();
            $filename = time() . '.' . $ext;
            $file->move(public_path('images/user'), $filename);
            $user->image = $filename;
        }

        $user->save();

        return response()->json(['success' => true, 'message' => 'Cập nhật thành công', 'data' => $user], 200);
    }

    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user)
            return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);

        // Xóa ảnh
        $path = public_path('images/user/' . $user->image);
        if ($user->image && file_exists($path)) {
            unlink($path);
        }

        $user->delete();
        return response()->json(['success' => true, 'message' => 'Xóa thành công'], 200);
    }
}