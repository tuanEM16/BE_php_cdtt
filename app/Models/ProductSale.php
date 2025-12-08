<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSale extends Model
{
    use HasFactory;

    // Laravel tự nối prefix thành 'lcte_product_sale'
    protected $table = 'product_sale'; 

    protected $fillable = [
        'name',
        'product_id', 
        'price_sale', 
        'date_begin', 
        'date_end', 
        'status', 
        'created_by', 
        'updated_by'
    ];

    // Quan hệ để lấy tên và ảnh sản phẩm
    public function product() {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}