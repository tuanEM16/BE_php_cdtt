<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $table = 'product_image'; // Laravel tự nối prefix lcte_
    public $timestamps = false; // QUAN TRỌNG: Tắt timestamps

    protected $fillable = [
        'product_id', 
        'image', 
        'alt', 
        'title'
    ];

    // Lấy thông tin sản phẩm
    public function product() {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}