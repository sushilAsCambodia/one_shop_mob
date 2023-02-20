<?php

namespace App\Models;

// use App\Traits\TranslationUtilities;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shipping extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = ['id'];
    //protected $hidden = ['created_at','updated_at','deleted_at'];

    public function shippingLogs(){
        return $this->hasOne(ShippingLog::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsToMany(Product::class,'order_product','order_id','product_id','order_id');
    }

    public function slotDeal()
    {
        return $this->hasOne(SlotDeal::class,'booking_id','booking_id');
    }

    public function toArray()
    {
        $attributes = parent::toArray();

        $attributes['deals'] = $this->slot_deal;
        if ($attributes['product']) {
            $attributes['product'] = $attributes['product'][0];
        }
        return $attributes;
    }
    
}
