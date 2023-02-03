<?php

namespace App\Models;

use App\Traits\DateSerializable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Translation extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $guarded = ['id'];
    protected $with = ['language'];
    //protected $hidden = ['created_at','updated_at','deleted_at'];

    public function translationable()
    {
        return $this->morphTo();
    }

    public function language(){
        return $this->belongsTo(Language::class,'language_id', 'id');
    }
}
