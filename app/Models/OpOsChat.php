<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasSnowflakePrimary;

class OpOsChat extends Model
{
    /** @use HasFactory<\Database\Factories\OpOsChatFactory> */
    use HasSnowflakePrimary;
}
