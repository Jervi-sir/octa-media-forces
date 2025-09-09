<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasSnowflakePrimary;

class OpBuying extends Model
{
    /** @use HasFactory<\Database\Factories\OpBuyingFactory> */
    use HasSnowflakePrimary;
    public function op()             { return $this->belongsTo(Op::class); }
    public function product()        { return $this->belongsTo(Product::class); }
    public function shippingAddress(){ return $this->belongsTo(OpShippingAddress::class, 'shipping_address_id'); }

}
