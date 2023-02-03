<?php

namespace App\Models;

use App\Traits\DateSerializable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use OwenIt\Auditing\Contracts\Auditable;

class Customer extends Authenticatable implements Auditable
{
    use HasFactory, SoftDeletes, HasApiTokens, DateSerializable;
    use \OwenIt\Auditing\Auditable;

    protected $guarded = ['id'];

    protected $hidden = [
        'password', 'created_at', 'updated_at', 'deleted_at',
    ];

    public function orders() {
        return $this->hasMany(Order::class);
    }

    public function orderSum() {
        return $this->hasMany(OrderProduct::class)->where(function($q) {
            $q->where('status','confirmed')->orWhere('status','winner');
        })->sum('amount');
    }

}
