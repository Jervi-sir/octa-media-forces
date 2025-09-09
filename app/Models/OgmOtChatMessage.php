<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasSnowflakePrimary;

class OgmOtChatMessage extends Model
{
    /** @use HasFactory<\Database\Factories\OgmOtChatMessageFactory> */
    use HasSnowflakePrimary;
}
