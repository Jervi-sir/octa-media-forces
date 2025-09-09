<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasSnowflakePrimary;

class OpCollection extends Model
{
    /** @use HasFactory<\Database\Factories\OpCollectionFactory> */
    use HasSnowflakePrimary;
    public function op()    { return $this->belongsTo(Op::class); }
    public function oss()   { return $this->belongsToMany(Os::class, 'op_collections_os'); }

}
