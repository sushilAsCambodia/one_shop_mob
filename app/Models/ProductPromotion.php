<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPromotion extends Model
{
    use HasFactory;
    protected $table="product_promotion";
    //protected $hidden = ['created_at','updated_at','deleted_at'];

    public $timestamps = false;

}
