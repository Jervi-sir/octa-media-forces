<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasSnowflakePrimary;

class OgmLogsData extends Model
{
    /** @use HasFactory<\Database\Factories\OgmLogsDataFactory> */
    use HasSnowflakePrimary;
    public function ogm()       { return $this->belongsTo(Op::class, 'ogm_id'); }
    public function logType()   { return $this->belongsTo(OgmLogsType::class, 'ogm_log_type_id'); }

}
