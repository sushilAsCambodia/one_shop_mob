<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Broadcast extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];

    public function translation()
    {
        return $this->morphMany(Translation::class, 'translationable')->where('language_id', request()->lang_id);
    }
    // public function toArray()
    // {
    //     $attributes = parent::toArray();
    //     $translateData = array();
    //     Language::all()->each(function ($language) use (&$translateData) {
    //         $tran = $this->translations()->whereLanguageId($language->id)->get();
    //         $oneLanguageData = array();
    //         $tran->each(function ($t) use (&$oneLanguageData, &$language){
    //             $oneLanguageData[$t->field_name] = $t->translation;
    //         });
    //         if(sizeof($oneLanguageData) > 0){
    //             $oneLanguageData['title']??$oneLanguageData['title']='';
    //             $oneLanguageData['description']??$oneLanguageData['description']='';
    //             $translateData[$language->locale_web] = $oneLanguageData;
    //         }else{
    //             $oneLanguageData['title'] = "";
    //             $oneLanguageData['description'] = '';
    //             $translateData[$language->locale_web] = $oneLanguageData;
    //         }
    //     });
    //     $attributes['translations'] = $translateData;
    //     return $attributes;
    // }
    
    public function toArray()
    {
        $attributes = parent::toArray();
        $translateData = array();
        Language::all()->each(function ($language) use (&$translateData) {
            $tran = $this->translation()->whereLanguageId($language->id)->get();
            $oneLanguageData = array();
            $tran->each(function ($t) use (&$oneLanguageData, &$language) {
                $oneLanguageData[$t->field_name] = $t->translation;
            });
            if (sizeof($oneLanguageData) > 0)
                $translateData = $oneLanguageData;
        });
        if (array_key_exists('favourite_count', $attributes)) {
            $attributes['favourite_count'] = count($attributes['favourite_count']);
        }

        $attributes['favourite'] = false;

        if (isset($_GET['customer_id'])) {
            $attributes['favourite'] = Favorite::where('product_id', $attributes['id'])->where('customer_id', $_GET['customer_id'])->exists();
        }

        $attributes['translation'] = $translateData ? $translateData : null;
        return $attributes;
    }
}
