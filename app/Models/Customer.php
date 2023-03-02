<?php

namespace App\Models;

use App\Traits\DateSerializable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Hash;

class Customer extends Authenticatable implements Auditable
{
    use HasRoles, HasFactory, SoftDeletes, HasApiTokens, DateSerializable, Notifiable;
    use \OwenIt\Auditing\Auditable;

    protected $guarded = ['id'];

    protected $hidden = [
        'password', 'created_at', 'updated_at', 'deleted_at',
    ];

    public $relationsToCascade = ['addresses', 'orders', 'invoice'];

    public function addresses()
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function orderSum()
    {
        return $this->hasMany(OrderProduct::class)->where(function ($q) {
            $q->where('status', 'confirmed')->orWhere('status', 'winner');
        })->sum('amount');
    }

    public function orderProduct()
    {
        return $this->hasMany(OrderProduct::class)->where('status', '!=', 'reserved');
    }

    protected function setPasswordAttribute($value)
    {
        return $this->attributes['password'] = Hash::make($value);
    }

    public function walletBalance()
    {
        return $this->hasOne(Wallet::class, 'member_id', 'id');
    }
}
