<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Category extends Model
{
    use HasFactory;
    protected $table = 'category';
    protected $fillable = [
        'name',
        'slug',
        'image',      
        'parent_id',  
        'sort_order',  
        'description', 
        'status',      
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];
}