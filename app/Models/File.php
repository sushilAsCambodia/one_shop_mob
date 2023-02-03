<?php

namespace App\Models;

use App\Traits\DateSerializable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class File extends Model implements Auditable
{
    use HasFactory, DateSerializable;
    use \OwenIt\Auditing\Auditable;

    protected $guarded = ['id'];
    //protected $hidden = ['created_at','updated_at','deleted_at','pivot'];
    protected $visible = ['path'];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at', 'fileable_id', 'fileable_type', 'type'];

    public function fileable()
    {
        return $this->morphTo();
    }

    public function getPathAttribute($attribute){
        $paths = explode('/', $attribute);
        if($paths[0] === 'public')
            return env('MLM_URL').'/api/media/'.$attribute;
        else
            return env('MLM_URL').'/'.$attribute;

    }

}
