<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Post extends Model
{
    use HasFactory;
    protected $table = 'post';
    protected $fillable = ['topic_id', 'title', 'slug', 'image', 'content', 'description', 'post_type', 'status', 'created_by', 'updated_by'];
}
