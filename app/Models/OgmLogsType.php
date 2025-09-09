<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasSnowflakePrimary;

class OgmLogsType extends Model
{
    /** @use HasFactory<\Database\Factories\OgmLogsTypeFactory> */
    public function logsData() { return $this->hasMany(OgmLogsData::class, 'ogm_log_type_id'); }
}
