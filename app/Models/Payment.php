<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = ['id'];
    //protected $hidden = ['created_at','updated_at','deleted_at'];

    protected $cast = [
        'request_data' => 'array',
        'response_data' => 'array',
    ];

    public function payment(){

        return $this->belongsTo(Order::class,'order_id','id');

    }
}
