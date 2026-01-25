<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Banner extends Model
{
    use HasFactory;
    protected $table = 'banner'; 
    protected $fillable = ['name', 'image', 'link', 'position', 'description', 'sort_order', 'status', 'created_by', 'updated_by'];
}
