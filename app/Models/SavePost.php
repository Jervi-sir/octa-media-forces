<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasSnowflakePrimary;

class SavePost extends Model
{
    /** @use HasFactory<\Database\Factories\SavePostFactory> */
    use HasSnowflakePrimary;
    public function op()        { return $this->belongsTo(Op::class); }
    public function product()   { return $this->belongsTo(Product::class); }
}
