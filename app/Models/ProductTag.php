<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductTag extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $table = "product_tag";
    //protected $hidden = ['created_at','updated_at','deleted_at'];

    public $timestamps = false;
}
