<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasSnowflakePrimary;

class OpShippingAddress extends Model
{
    /** @use HasFactory<\Database\Factories\OpShippingAddressFactory> */
    use HasSnowflakePrimary;
    public function op()    { return $this->belongsTo(Op::class); }
    public function wilaya(){ return $this->belongsTo(Wilaya::class); }

}
