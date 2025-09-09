<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasSnowflakePrimary;

class OpCollectionsOs extends Model
{
    /** @use HasFactory<\Database\Factories\OpCollectionsOFactory> */
    use HasSnowflakePrimary;
    public function opCollection()   { return $this->belongsTo(OpCollection::class); }
    public function os()             { return $this->belongsTo(Os::class); }

}
