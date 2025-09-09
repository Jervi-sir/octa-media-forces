<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TagNetwork extends Model
{
    /** @use HasFactory<\Database\Factories\TagNetworkFactory> */
    public function tag()            { return $this->belongsTo(Tag::class); }
}
