<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderProduct extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'order_product';
    protected $guarded = ['id'];
    //protected $hidden = ['created_at','updated_at','deleted_at'];


    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function products()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
