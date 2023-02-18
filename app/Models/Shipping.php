<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shipping extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = ['id'];
    //protected $hidden = ['created_at','updated_at','deleted_at'];

    public function shippingLogs(){
        return $this->hasMany(ShippingLog::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->hasOneThrough(Product::class,OrderProduct::class,'order_id','id','product_id');
    }

    public function slotDeal()
    {
        return $this->hasOne(SlotDeal::class,'booking_id','booking_id');
    }


    public function toArray()
    {
        $attributes = parent::toArray();
        
        $attributes['deals'] = $this->order->deals;
        return $attributes;
    }

}
