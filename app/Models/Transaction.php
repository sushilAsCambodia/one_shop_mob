<?php

namespace App\Models;

use App\Traits\DateSerializable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Transaction extends Model implements Auditable
{
    use HasFactory, SoftDeletes, DateSerializable;
    use \OwenIt\Auditing\Auditable;

    protected $guarded = ['id'];

    public function image()
    {
        return $this->morphOne(File::class, 'fileable');
    }

}
