<?php
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
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
Route::apiResource('product_attribute', AttributeController::class);
Route::apiResource('product_sale', AttributeController::class);
Route::apiResource('product_image', AttributeController::class);
Route::apiResource('product_store', AttributeController::class);

Route::get('config', [ConfigController::class, 'index']);
Route::post('config/update', [ConfigController::class, 'update']);