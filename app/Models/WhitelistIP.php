<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class WhitelistIP extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'whitelist_ips';
    //protected $hidden = ['created_at','updated_at','deleted_at'];

    protected $guarded = ['id'];

    const ALLOWED_IPS = ['106.'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
