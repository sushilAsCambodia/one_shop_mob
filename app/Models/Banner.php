<?php

namespace App\Models;

use App\Traits\DateSerializable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model
{
    use HasFactory, SoftDeletes, DateSerializable;

    protected $guarded = ['id'];
    protected $with = ['image'];
    //protected $hidden = ['created_at','updated_at','deleted_at'];

    public function image()
    {
        return $this->morphOne(File::class, 'fileable');
    }
    
    public function translation()
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function toArray()
    {
        $attributes = parent::toArray();
        $translateData = array();
        Language::all()->each(function ($language) use (&$translateData) {
            $tran = $this->translation()->whereLanguageId($language->id)->get();
            $oneLanguageData = array();
            $tran->each(function ($t) use (&$oneLanguageData, &$language){
                $oneLanguageData[$t->field_name] = $t->translation;
            });
            if(sizeof($oneLanguageData) > 0)
                $translateData = $oneLanguageData;

        }); 
        $attributes['translation'] = $translateData ? $translateData : null;
        return $attributes;
    }
}
