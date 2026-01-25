<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
class ProductSale extends Model
{
    use HasFactory;
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
    public function product() {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}