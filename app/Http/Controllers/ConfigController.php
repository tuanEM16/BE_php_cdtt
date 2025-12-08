<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Config;

class ConfigController extends Controller
{
    // Lấy thông tin cấu hình (Luôn lấy dòng đầu tiên)
    public function index()
    {
        $config = Config::first();
        
        // Nếu chưa có dữ liệu thì tạo dòng mặc định
        if (!$config) {
            $config = new Config();
            $config->site_name = 'Tên Website Mặc định';
            $config->email = 'admin@example.com';
            $config->phone = '0123456789';
            $config->hotline = '1900xxxx';
            $config->address = 'Hồ Chí Minh, Việt Nam';
            $config->status = 1;
            $config->save();
        }

        return response()->json(['success' => true, 'data' => $config], 200);
    }

    // Cập nhật cấu hình
    public function update(Request $request)
    {
        $config = Config::first();
        if (!$config) {
            $config = new Config();
        }

        $config->site_name = $request->site_name;
        $config->email = $request->email;
        $config->phone = $request->phone;
        $config->hotline = $request->hotline;
        $config->address = $request->address;
        $config->status = $request->status ?? 1;

        $config->save();

        return response()->json(['success' => true, 'message' => 'Cập nhật thành công', 'data' => $config], 200);
    }
}