<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductStore extends Model
{
    use HasFactory;

    // Laravel tự nối prefix thành 'lcte_product_store'
    protected $table = 'product_store'; 

    protected $fillable = [
        'product_id', 
        'price_root', // Giá nhập (giá vốn)
        'qty',        // Số lượng nhập
        'status', 
        'created_by', 
        'updated_by'
    ];

    // Lấy thông tin sản phẩm để hiển thị tên
    public function product() {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}