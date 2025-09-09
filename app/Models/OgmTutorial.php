<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OgmTutorial extends Model
{
    /** @use HasFactory<\Database\Factories\OgmTutorialFactory> */
    protected $casts = [
        'posted_at' => 'datetime',
    ];
    public function ogm() { return $this->belongsTo(Op::class, 'ogm_id'); }

}
