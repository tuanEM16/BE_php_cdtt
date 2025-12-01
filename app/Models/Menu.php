<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $table = 'menu'; 

    protected $fillable = [
        'name',
        'link',
        'type',       
        'parent_id',  
        'sort_order',
        'table_id',   
        'position',   
        'status',
        'created_by',
        'updated_by'
    ];
}