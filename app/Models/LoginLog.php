<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    //protected $hidden = ['created_at','updated_at','deleted_at'];

    public function user()
    {
        return $this->morphTo();
    }
}
