<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasSnowflakePrimary;

class Draft extends Model
{
    /** @use HasFactory<\Database\Factories\DraftFactory> */
    use HasFactory,  HasSnowflakePrimary;

    protected $fillable = ['ogm_id', 'os_id', 'image_url'];
    protected $casts = [
        'id' => 'string',
        'os_id' => 'string',
    ];

    public function ogm()
    {
        return $this->belongsTo(Ogm::class);
    }
}
