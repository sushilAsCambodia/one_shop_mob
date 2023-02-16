<?php

namespace App\Models;

use App\Traits\DateSerializable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promotion extends Model
{
    use HasFactory, SoftDeletes, DateSerializable;

    protected $guarded = ['id'];
    //protected $hidden = ['created_at','updated_at','deleted_at'];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at', 'pivot'];

    public function image()
    {
        return $this->morphOne(File::class, 'fileable');
    }

    public function translation()
    {
        return $this->morphMany(Translation::class, 'translationable')->where('language_id', request()->lang_id);
    }
}
