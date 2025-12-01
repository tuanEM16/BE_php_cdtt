<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'product'; 

    protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'slug',
        'price_buy', 
        'price_sale',
        'thumbnail', 
        'qty',
        'detail',
        'description',
        'status',
        'created_by',
        'updated_by'
    ];
}