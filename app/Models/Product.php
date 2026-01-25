<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use App\Models\ProductImage;
use App\Models\ProductAttribute;
use App\Models\ProductStore;
use App\Models\ProductSale;
class Product extends Model
{
    use HasFactory;
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
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
    public function product_images()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'id');
    }
    public function product_attributes()
    {
        return $this->hasMany(ProductAttribute::class, 'product_id', 'id');
    }
    public function productStores()
    {
        return $this->hasMany(ProductStore::class, 'product_id', 'id');
    }
    public function sale()
    {
        return $this->hasOne(ProductSale::class, 'product_id', 'id')
            ->where('date_begin', '<=', now()) // Đã bắt đầu
            ->where('date_end', '>=', now())   // Chưa kết thúc
            ->where('status', 1)               // Đang bật
            ->orderBy('price_sale', 'ASC');    // Lấy giá giảm sâu nhất nếu trùng đợt
    }
}