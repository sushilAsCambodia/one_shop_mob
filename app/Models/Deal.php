<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deal extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];
    //protected $hidden = ['created_at','updated_at','deleted_at'];

    protected $hidden = ['updated_at', 'deleted_at', 'product_id', 'is_bot'];

    public function products(){
        return $this->hasOne(Product::class, 'id', 'product_id');
    }

    public function slots(){
        return $this->hasOne(Slot::class, 'id', 'slot_id');
    }

    public function orders(){
        return $this->belongsToMany(Order::class,'slot_deals');
    }

    public function customer() {
        return $this->hasManyThrough(Customer::class, Order::class, 'order_id', 'id', 'deal_id', 'customer_id');
    }
}
