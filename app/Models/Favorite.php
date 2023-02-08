<?php

namespace App\Models;

use App\Traits\DateSerializable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Favorite extends Model implements Auditable
{
    use HasFactory, SoftDeletes, DateSerializable;
    use \OwenIt\Auditing\Auditable;

    protected $guarded = ['id'];
    //protected $hidden = ['created_at','updated_at','deleted_at'];

    public function products(){
        return $this->hasMany(Product::class)->with([
            'image',
            'translation',
            'tags',
            'deal.slots',
            'favouriteCount',
        ]);
    }
}
