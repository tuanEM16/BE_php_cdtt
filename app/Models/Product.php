<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// --- QUAN TRỌNG: Phải import các Model liên quan ---
use App\Models\Category;
use App\Models\ProductImage;
use App\Models\ProductAttribute;

class Product extends Model
{
    use HasFactory;

    // Tên bảng (Nếu bạn đã config prefix 'lcte_' trong database.php thì chỉ cần để 'product')
    protected $table = 'product'; 

    protected $fillable = [
        'name',
        'slug',
        'category_id',
        'thumbnail',
        'content',
        'description',
        'price_buy',
        'qty',
        'status',
        'created_by',
        'updated_by'
    ];

    // --- CÁC MỐI QUAN HỆ (RELATIONSHIPS) ---

    // 1. Quan hệ với Category (1 sản phẩm thuộc 1 danh mục)
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    // 2. Quan hệ với ProductImage (1 sản phẩm có nhiều ảnh phụ)
    // Tên hàm 'product_images' phải khớp với chữ trong with('product_images') ở Controller
    public function product_images()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'id');
    }

    // 3. Quan hệ với ProductAttribute (1 sản phẩm có nhiều thuộc tính)
    // Tên hàm 'product_attributes' phải khớp với chữ trong with('product_attributes') ở Controller
    public function product_attributes()
    {
        return $this->hasMany(ProductAttribute::class, 'product_id', 'id');
    }
}