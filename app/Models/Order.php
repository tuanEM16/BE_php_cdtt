<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;
    protected $table = 'order';
    protected $fillable = ['user_id', 'name', 'email', 'phone', 'address', 'note', 'status', 'created_by', 'updated_by'];
    
    // Relationship để lấy chi tiết đơn hàng
    public function orderdetails() {
        return $this->hasMany(OrderDetail::class, 'order_id', 'id');
    }
}
