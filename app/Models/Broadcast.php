<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Broadcast extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translationable')->where('language_id', request()->lang_id);
    }
    public function toArray()
    {
        $attributes = parent::toArray();
        $translateData = array();
        Language::all()->each(function ($language) use (&$translateData) {
            $tran = $this->translations()->whereLanguageId($language->id)->get();
            $oneLanguageData = array();
            $tran->each(function ($t) use (&$oneLanguageData, &$language){
                $oneLanguageData[$t->field_name] = $t->translation;
            });
            if(sizeof($oneLanguageData) > 0){
                $oneLanguageData['title']??$oneLanguageData['title']='';
                $oneLanguageData['description']??$oneLanguageData['description']='';
                $translateData[$language->locale_web] = $oneLanguageData;
            }else{
                $oneLanguageData['title'] = "";
                $oneLanguageData['description'] = '';
                $translateData[$language->locale_web] = $oneLanguageData;
            }
        });
        $attributes['translations'] = $translateData;
        return $attributes;
    }
}
