<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasSnowflakePrimary;

class OgmOperationTest extends Model
{
    /** @use HasFactory<\Database\Factories\OgmOperationTestFactory> */
    use HasSnowflakePrimary;
    public function ogm() { return $this->belongsTo(Ogm::class); }

}
