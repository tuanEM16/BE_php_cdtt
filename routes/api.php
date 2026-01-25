<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderDetailController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\ProductAttributeController;
use App\Http\Controllers\ProductSaleController;
use App\Http\Controllers\ProductStoreController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\VNPayController;
/*
|--------------------------------------------------------------------------
| PHẦN 1: PUBLIC ROUTES - Dành cho giao diện (main)
|--------------------------------------------------------------------------
| Khớp với các trang: Home, Product List/Detail, Blog, Cart...
| Ai cũng có thể truy cập mà không cần Token.
*/
Route::post('login', [UserController::class, 'login']);
Route::post('register', [UserController::class, 'register']);
Route::get('product', [ProductController::class, 'index']); // Trang chủ & trang sản phẩm
Route::get('product/new', [ProductController::class, 'product_new']);
Route::get('product/{id}', [ProductController::class, 'show']); // Trang chi tiết [id]
Route::get('category', [CategoryController::class, 'index']);
Route::get('category/{id}', [CategoryController::class, 'show']);
Route::get('banner', [BannerController::class, 'index']);
Route::get('topic', [TopicController::class, 'index']);
Route::get('post', [PostController::class, 'index']);
Route::get('post/{id}', [PostController::class, 'show']);
Route::get('menu', [MenuController::class, 'index']); // Để hiển thị Navbar
Route::get('config', [ConfigController::class, 'index']); // Để hiển thị Footer/Logo
Route::post('/vnpay/create-payment-url', [VNPayController::class, 'createPaymentUrl'])->middleware('auth:sanctum');
Route::get('/vnpay/ipn', [VNPayController::class, 'ipn']);
Route::get('vnpay/return', [VNPayController::class, 'handleReturn']);
Route::post('contact/store', [ContactController::class, 'store']);
/*
|--------------------------------------------------------------------------
| PHẦN 2: CUSTOMER ROUTES - Dành cho (main)/user & (main)/cart
|--------------------------------------------------------------------------
| Phải đăng nhập mới làm được (có Token).
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::post('user/change-password', [UserController::class, 'changePassword']);
    Route::post('profile/update', [UserController::class, 'updateProfile']);
    Route::put('/orders/pending-shipping', [OrderController::class, 'updatePendingShipping']);
    Route::put('order/{order}/address', [OrderController::class, 'updateAddress']);
    Route::put('order/{order}/cancel', [OrderController::class, 'cancel']);
    Route::post('order/checkout', [OrderController::class, 'store']);
    Route::get('profile', [UserController::class, 'profile']);
    Route::post('logout', [UserController::class, 'logout']);
    Route::get('order/history', [OrderController::class, 'getHistory']);
});
/*
|--------------------------------------------------------------------------
| PHẦN 3: ADMIN ROUTES - Dành cho (dashboard)/admin
|--------------------------------------------------------------------------
| Tương ứng với toàn bộ thư mục admin của bạn.
| Yêu cầu: Đăng nhập + Có quyền Admin.
*/
Route::middleware(['auth:sanctum', 'isAdmin'])->prefix('admin')->group(function () {
    Route::post('product/store', [ProductController::class, 'store']);
    Route::post('product/update/{id}', [ProductController::class, 'update']);
    Route::delete('product/destroy/{id}', [ProductController::class, 'destroy']);
    Route::apiResource('productimage', ProductImageController::class);      // admin/productimage
    Route::apiResource('productattribute', ProductAttributeController::class); // admin/productattribute
    Route::apiResource('productsale', ProductSaleController::class);        // admin/productsale
    Route::apiResource('productstore', ProductStoreController::class);      // admin/productstore
    Route::apiResource('attribute', AttributeController::class);            // admin/attribute
    Route::post('category/store', [CategoryController::class, 'store']);
    Route::post('category/update/{id}', [CategoryController::class, 'update']); // Dùng POST để an toàn file ảnh
    Route::delete('category/destroy/{id}', [CategoryController::class, 'destroy']);
    Route::get('order', [OrderController::class, 'index']); // Xem danh sách
    Route::get('order/{id}', [OrderController::class, 'show']); // Xem chi tiết
    Route::delete('order/{id}', [OrderController::class, 'destroy']);
    Route::apiResource('orderdetail', OrderDetailController::class);
    Route::post('order/update/{id}', [OrderController::class, 'update']);
    Route::apiResource('user', UserController::class);
    Route::apiResource('post', PostController::class)->except(['index', 'show']); // index/show đã có ở public
    Route::apiResource('topic', TopicController::class)->except(['index', 'show']);
    Route::apiResource('banner', BannerController::class)->except(['index']);
    Route::apiResource('menu', MenuController::class)->except(['index']);
    Route::post('config/update', [ConfigController::class, 'update']);
    Route::get('contact', [ContactController::class, 'index']);
    Route::get('contact/{id}', [ContactController::class, 'show']);
    Route::delete('contact/{id}', [ContactController::class, 'destroy']);
    Route::post('contact/reply/{id}', [ContactController::class, 'reply']);
});