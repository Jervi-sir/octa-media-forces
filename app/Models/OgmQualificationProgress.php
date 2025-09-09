<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasSnowflakePrimary;

class OgmQualificationProgress extends Model
{
    use HasSnowflakePrimary;
    protected $fillable = ['ogm_id', 'status', 'progress'];
    protected $casts = [
        'id' => 'string',
        'ogm_id' => 'string',
        'progress' => 'json'
    ];
}
