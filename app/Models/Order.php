<?php

namespace App\Models;

use App\Models\Customer;
use App\Models\OrderProduct;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $guarded = [];

    // public function productVariants()
    // {
    //     return $this->hasMany(ProductVariant::class,'product_id','id');
    // }

    public function orderVariants()
    {
        return $this->hasMany(OrderProduct::class,'order_id','id');
    }

    public function orderCustomer()
    {
        return $this->hasOne(Customer::class,'order_id','id');
    }

    public function scopeCancel($query)
    {
        return $query->where(['payment' => 5]);
    }

    public function scopePaid($query)
    {
        return $query->where(['payment' => 1]);
    }
}
