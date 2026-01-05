<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductAttributeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\ProductStoreController;
use App\Http\Controllers\ProductSaleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\OrderDetailController;
Route::get('/product/new', [ProductController::class, 'product_new']);

Route::apiResource('banner', BannerController::class);
Route::apiResource('topic', TopicController::class);
Route::apiResource('post', PostController::class);
Route::apiResource('order', OrderController::class);
Route::apiResource('contact', ContactController::class);
Route::apiResource('menu', MenuController::class);
Route::apiResource('user', UserController::class);
Route::apiResource('product', ProductController::class);
Route::apiResource('category', CategoryController::class);
Route::apiResource('attribute', AttributeController::class);
Route::apiResource('productattribute', ProductAttributeController::class);
Route::apiResource('productsale', ProductSaleController::class);
Route::apiResource('productimage', ProductImageController::class);
Route::apiResource('productstore', ProductStoreController::class);
Route::apiResource('orderdetail', OrderDetailController::class);

Route::get('config', [ConfigController::class, 'index']);
Route::post('config/update', [ConfigController::class, 'update']);

Route::post('/login', [UserController::class, 'login']);
Route::post('/register', [UserController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [UserController::class, 'logout']);
    Route::get('/profile', [UserController::class, 'profile']);
    Route::apiResource('user', UserController::class); 
});

