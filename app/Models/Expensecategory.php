<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expensecategory extends Model
{

     protected $guarded = [];
    use HasFactory;


     public function expense()
     {
        return $this->hasMany(Expense::class,'expensecategory_id');
     }


}
