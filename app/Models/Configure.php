<?php

namespace App\Models;

use App\Traits\DateSerializable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Configure extends Model implements Auditable
{
    use HasFactory, DateSerializable, SoftDeletes;
    use \OwenIt\Auditing\Auditable;
    protected $guarded = ['id'];
    //protected $hidden = ['created_at','updated_at','deleted_at'];


    public function configurable()
    {
        return $this->morphTo();
    }

}
