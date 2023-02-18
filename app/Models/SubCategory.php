<?php

namespace App\Models;

use App\Traits\DateSerializable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class SubCategory extends Model implements Auditable
{
    use HasFactory, SoftDeletes, DateSerializable;
    use \OwenIt\Auditing\Auditable;

    protected $guarded = ['id'];
    protected $with = ['image', 'translation'];
    //protected $hidden = ['created_at','updated_at','deleted_at'];

    protected $hidden = ['parent_sub_category_id', 'created_at', 'updated_at', 'deleted_at', 'description', 'name'];

    public function subCategories(){
        return $this->hasMany(SubCategory::class,'parent_sub_category_id', 'id');
    }

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function products(){
        $query = $this->hasMany(Product::class)->with([
            'image',
            'translation',
            'tags',
            'deal.slots',
            'favouriteCount',
        ]);

        $query = $query->whereHas('deal', function ($query) {
            $query->whereIn('status', ['expired', 'active']);
        });

        return $query;

    }
    public function image()
    {
        return $this->morphOne(File::class, 'fileable');
    }

    public function translation()
    {
        return $this->morphOne(Translation::class, 'translationable')->where('language_id', request()->lang_id);
    }
    public function translates()
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
        $attributes['translation'] = $translateData;
        return $attributes;
    }

}
