<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasSnowflakePrimary;

class OgmPayment extends Model
{
    /** @use HasFactory<\Database\Factories\OgmPaymentFactory> */
    use HasSnowflakePrimary;
    public function ogm() { return $this->belongsTo(Op::class, 'ogm_id'); }

}
