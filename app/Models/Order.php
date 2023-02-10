<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = ['id'];
    //protected $hidden = ['created_at','updated_at','deleted_at'];

    public function orderProduct()
    {
        return $this->hasMany(OrderProduct::class , 'status', 'status');
    }
    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class);
    }
    public function deals() {
        return $this->belongsToMany(Deal::class, 'slot_deal', 'order_id', 'deal_id');
    }
    public function customer() {
        return $this->belongsTo(Customer::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_product')->with(['image']);
    }

    public function shipping()
    {
        return $this->hasOne(Shipping::class);
    }

    public function delivered_products()
    {
        return $this->products();
    }

}
