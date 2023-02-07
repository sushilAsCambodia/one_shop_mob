<?php

namespace App\Models;

use App\Traits\DateSerializable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;
use OwenIt\Auditing\Contracts\Auditable;

class Product extends Model implements Auditable
{
    use HasFactory, SoftDeletes, DateSerializable;
    use \OwenIt\Auditing\Auditable;

    protected $guarded = ['id'];

    // protected $with = ['category', 'subCategory','image','translation', 'tags','deal','deal.slots','favouriteCount','promotion'];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function favouriteCount()
    {
        return $this->hasMany(Favorite::class);
    }


    public function image()
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public function dealConfiguration()
    {
        return $this->morphOne(Configure::class, 'configurable');
    }

    public function translation()
    {
        return $this->morphMany(Translation::class, 'translationable')->where('language_id', request()->lang_id);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function prices()
    {
        return $this->hasMany(ProductCurrency::class, 'product_id', 'id')->where('currency_id', request()->cur_id);
    }

    public function slot()
    {
        // return $this->hasOneThrough(Slot::class,Deal::class, 'slot_id', 'id');
        $query = $this->belongsToMany(Slot::class, 'deals')->distinct('slots.id');
        return $query;
    }

    public function slot2()
    {
        // return $this->hasOneThrough(Slot::class,Deal::class, 'slot_id', 'id');
        $query = $this->belongsToMany(Slot::class, 'deals');
        $session = Session::get("promotional_query_session");
        if ($session)
            $query->where('deals.status', '!=', 'settled');

        return $query;
    }

    public function deal()
    {
        return $this->hasOne(Deal::class)->where('status', 'active');
    }
    public function deals()
    {
        return $this->hasMany(Deal::class);
    }
    public function inventory()
    {
        return $this->hasOne(Inventory::class);
    }
    public function promotion()
    {
        return $this->belongsToMany(Promotion::class);
    }

    public function slotDeals()
    {

        $query = $this->hasManyThrough(SlotDeal::class, Deal::class);
        $orderID = Session::get('product_order_id');
        if ($orderID)
            $query->where('slot_deals.order_id', $orderID);

        return $query;
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_product');
    }

    public function shipping()
    {
        return $this->hasOneThrough(Shipping::class, Order::class, 'id', 'order_id');
    }

    public function toArray()
    {
        //=================================dynamic hidden fields================================
        // $querySession = Session::get("promotional_query_session");
        // $querySession = Session::forget("promotional_query_session");

        // if($querySession){
        //     $this->hidden = ['created_at','updated_at','deleted_at',"category_id","sub_category_id","meta_description","meta_keywords","meta_title",'favourite_count','slot_deals','slotDeals','promotion','favouriteCount','inventory'];
        // }
        // else{
        //     $this->hidden = ['created_at','updated_at','deleted_at',"category_id","sub_category_id"];

        // }
        //=================================dynamic hidden fields================================

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
        if (array_key_exists('deal', $attributes)) {
            if (sizeof($attributes['slot']) > 0) {
                $deal = (array) $attributes['deal'];
                if ($deal) {
                    $attributes['slot'] = $deal['slots'];
                }
            }
        }

        /* if(sizeof($attributes['slot']) > 0){
            $attributes['slot'] = $attributes['slot'][0];
        } */
        if (array_key_exists('favourite_count', $attributes)) {
            $attributes['favourite_count'] = count($attributes['favourite_count']);
        }

        $attributes['favourite'] = false;

        if (isset($_GET['customer_id'])) {
            $attributes['favourite'] = Favorite::where('product_id', $attributes['id'])->where('customer_id', $_GET['customer_id'])->exists();
        }




        $attributes['translation'] = $translateData;
        return $attributes;
    }
}
