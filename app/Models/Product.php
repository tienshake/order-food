<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'price', 'image', 'is_active', 'is_featured', 'on_sale', 'category_id'];

    protected $casts = [
        'image' => 'string',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }


    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }
}
