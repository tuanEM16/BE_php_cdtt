<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
class UserController extends Controller
{
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
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:user',
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
        $user->username = $request->username ?? strstr($request->email, '@', true); // Tự tạo username từ email nếu ko có
        $user->password = Hash::make($request->password);
        $user->roles = 'customer';
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

    // 4. Lấy thông tin bản thân (Profile)
    public function profile(Request $request)
    {
        return response()->json(['success' => true, 'data' => $request->user()]);
    }
    public function index(Request $request) 
    {
        $currentUser = $request->user(); 

        if ($currentUser->roles != 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập dữ liệu này'
            ], 403);
        }
        // -----------------------------

        $users = User::where('status', '!=', 0)
            ->orderBy('created_at', 'DESC')
            ->paginate(20);

        return response()->json(['success' => true, 'message' => 'Tải danh sách thành công', 'data' => $users], 200);
    }

    public function store(Request $request)
    {
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->username = $request->username;
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
        if (!$user)
            return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        return response()->json(['success' => true, 'data' => $user], 200);
    }

    // 4. Cập nhật
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user)
            return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);

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
            if ($user->avatar && file_exists($oldPath))
                unlink($oldPath);

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
        if (!$user)
            return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);

        $path = public_path('images/user/' . $user->avatar);
        if ($user->avatar && file_exists($path))
            unlink($path);

        $user->delete();
        return response()->json(['success' => true, 'message' => 'Xóa thành công'], 200);
    }
}