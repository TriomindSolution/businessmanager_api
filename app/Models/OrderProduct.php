<?php

namespace App\Models;

use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderProduct extends Model
{
    use HasFactory;

    protected $guarded = [];

    // public function productVariants()
    // {
    //     return $this->hasMany(ProductVariant::class,'product_id','id');
    // }

    public function orders(){
        return $this->belongsTo(Order::class);
    }
}
