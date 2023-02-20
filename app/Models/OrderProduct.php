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
        return $this->belongsTo(Product::class)->with(['image']);
    }

    // public function products()
    // {
    //     return $this->hasMany(Product::class, 'id', 'product_id');
    // }
    // ->with(['image', 'deal.slots'])
    // public function dealIds()
    // {
    //     return $this->hasOne(SlotDeal::class, 'order_id')->distinct('slot_deals.deal_id');
    // }

    public function toArray()
    {
        $attributes = parent::toArray();

        // $attributes['delivered_products'] = $this->products()->whereHas('shipping',function ($query) use ($attributes) {
        //                                         $query->where('shippings.status','Delivered')->where('shippings.order_id', $attributes['id']);
        //                                     })->get();
        
        // $attributes['slotDealsCount'] = SlotDeal::where('order_id', $this->order_id)->where('deal_id', $this->deal_id)->get()->count();
        $attributes['slotDealsCounts'] = SlotDeal::where('order_id', $this->order_id)->where('deal_id', $this->deal_id)->get()->count();
        
        return $attributes;
    }
}
