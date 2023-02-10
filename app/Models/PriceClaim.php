<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class PriceClaim extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = ['id'];
    //protected $hidden = ['created_at','updated_at','deleted_at'];


    public function product()
    {
        return $this->belongsTo(Product::class)->with(['image', 'translation', 'deal.slots']);
    }


    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function slot_deals()
    {
        return $this->hasOne(SlotDeal::class, 'booking_id', 'booking_id');
    }
    public function order()
    {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }

    public function toArray()
    {
        $attributes = parent::toArray();

        // $productSlotDeals = new stdClass();
        $product = $this->product;
        if ($product) {
            // $productData['product_id'] = $product->id;
            // $productData['slug'] = $product->slug;
            // $productData['deal'] = $this->product->deals;
            $order = (array) $attributes['order'];
            Session::put('product_order_id', $order['order_id']);
            $slotDeals = [];
            if(array_key_exists('order_products', $order)) {
                foreach ($order['order_products'] as $p) {
                    if ($p->deal->product_id === $this->deal_id)
                        array_push($slotDeals, $p);
                }
            }
            $attributes['order_products'] = $order['order_products'];
            // $productData['slot_deals'] = $slotDeals;
            // $productData['slot_deals_count'] = count($slotDeals);
            // array_push($productSlotDeals, $productData);
            // $attributes['product_slot_deals'] = $productData;
            $attributes['order_table_id'] = $this->order->order_id;
        }

        return $attributes;
    }


}
