<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasSnowflakePrimary;

class OpContactList extends Model
{
    /** @use HasFactory<\Database\Factories\OpContactListFactory> */
    use HasSnowflakePrimary;
    public function op() { return $this->belongsTo(Op::class); }

}
