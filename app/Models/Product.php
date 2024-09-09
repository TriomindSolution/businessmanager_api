<?php

namespace App\Models;

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function productVariants()
    {
        return $this->hasMany(ProductVariant::class,'product_id','id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
