<?php

namespace App\Models;

use App\Traits\DateSerializable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Category extends Model implements Auditable
{
    use HasFactory, SoftDeletes, DateSerializable;
    use \OwenIt\Auditing\Auditable;

    protected $guarded = ['id'];
    protected $with = ['image', 'translation'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at', 'description'];

    public function products(){
        return $this->hasMany(Product::class);
    }
    
    public function cateProducts(){
        return $this->hasMany(Product::class)->where('sub_category_id', '=', null);
    }

    public function subCategories(){
        return $this->hasMany(SubCategory::class);
    }

    public function image()
    {
        return $this->morphOne(File::class, 'fileable');
    }

    public function translation()
    {
        return $this->morphOne(Translation::class, 'translationable')->where('language_id', request()->lang_id);
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
