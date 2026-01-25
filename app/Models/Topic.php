<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Topic extends Model
{
    use HasFactory;
    protected $table = 'topic'; // Mapping với bảng lcte_topic
    protected $fillable = [
        'name',
        'slug',
        'sort_order',
        'description',
        'status',
        'created_by',
        'updated_by'
    ];
}