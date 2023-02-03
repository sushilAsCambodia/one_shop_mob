<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $casts = [
        'data' => 'array',
        'id' => 'string'
    ];
    //protected $hidden = ['created_at','updated_at','deleted_at'];

    public function getDataAttribute($value)
    {
        return json_decode($value);
    }
}
