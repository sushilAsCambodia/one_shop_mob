<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class WinnerDetail extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $guarded = ['id'];
    //protected $hidden = ['created_at','updated_at','deleted_at'];

    public function customer()
    {
        return $this->hasOne(Customer::class,'id','customer_id');

    }
    public function order()
    {
        return $this->hasOne(Order::class,'order_id','order_id');

    }
    public function product()
    {
        return $this->hasOneThrough(Product::class,OrderProduct::class,'product_id','id');

    }
}
