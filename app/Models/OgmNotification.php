<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasSnowflakePrimary;

class OgmNotification extends Model
{
    /** @use HasFactory<\Database\Factories\OgmNotificationFactory> */
    use HasSnowflakePrimary;
    protected $fillable = ['ogm_id', 'ogm_notification_type_id', 'content', 'is_opened'];

    protected $casts = [
        'id' => 'string',
        'ogm_id' => 'string',
        'time' => 'datetime'
    ];

    public function ogm()   { return $this->belongsTo(Ogm::class, 'ogm_id'); }
    public function ogmNotificationType() {
        return $this->belongsTo(OgmNotificationType::class, 'ogm_notification_type_id');
    }
}
