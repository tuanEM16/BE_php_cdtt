<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
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
    public function updateProfile(Request $request)
    {
        $user = $request->user(); // user từ token sanctum
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'username' => 'required|string|max:255|unique:user,username,' . $user->id,
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
        $user->name = $validated['name'];
        $user->phone = $validated['phone'];
        $user->username = $validated['username'];
        if ($request->hasFile('avatar')) {
            $folder = public_path('uploads/avatars');
            if (!is_dir($folder))
                mkdir($folder, 0777, true);
            if (!empty($user->avatar)) {
                $old = $folder . '/' . $user->avatar;
                if (file_exists($old))
                    unlink($old);
            }
            $file = $request->file('avatar');
            $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $file->move($folder, $filename);
            $user->avatar = $filename;
        }
        $user->updated_at = now();
        $user->updated_by = $user->id;
        $user->save();
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'username' => $user->username,
                'role' => $user->roles, // ✅ FE của mày đang dùng user.role
                'avatar' => $user->avatar ? url('uploads/avatars/' . $user->avatar) : null, // ✅ trả về URL luôn
            ]
        ]);
    }
    public function profile(Request $request)
    {
        return response()->json(['success' => true, 'data' => $request->user()]);
    }
    public function index(Request $request)
    {
        $users = User::orderBy('created_at', 'DESC')->paginate(20);
        return response()->json(['success' => true, 'message' => 'Tải danh sách thành công', 'data' => $users], 200);
    }
    public function store(Request $request)
    {
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
        $user->created_by = Auth::id() ?? 1;
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
    public function changePassword(Request $request)
    {
        $user = $request->user(); // auth:sanctum
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Chưa đăng nhập'], 401);
        }
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi nhập liệu',
                'errors' => $validator->errors()
            ], 422);
        }
        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Mật khẩu hiện tại không đúng'
            ], 400);
        }
        if (Hash::check($request->new_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Mật khẩu mới không được trùng mật khẩu cũ'
            ], 400);
        }
        $user->password = Hash::make($request->new_password);
        $user->updated_at = now();
        $user->save();
        return response()->json([
            'success' => true,
            'message' => 'Đổi mật khẩu thành công'
        ], 200);
    }
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user)
            return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
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
        if ($request->username)
            $user->username = $request->username;
        if ($request->roles)
            $user->roles = $request->roles;
        if ($request->status)
            $user->status = $request->status;
        $user->updated_at = now();
        $user->updated_by = Auth::id() ?? 1;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        if ($request->hasFile('image')) {
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
        $path = public_path('images/user/' . $user->image);
        if ($user->image && file_exists($path)) {
            unlink($path);
        }
        $user->delete();
        return response()->json(['success' => true, 'message' => 'Xóa thành công'], 200);
    }
}