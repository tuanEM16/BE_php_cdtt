<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class OrderDetail extends Model
{
    use HasFactory;
    protected $table = 'order_detail'; // Laravel tự nối prefix thành 'lcte_order_detail'
    public $timestamps = false; // Bảng này không có created_at, updated_at
    protected $fillable = [
        'order_id', 
        'product_id', 
        'price', 
        'qty', 
        'amount', 
        'discount'
    ];
    public function product() {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
    public function order() {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
}