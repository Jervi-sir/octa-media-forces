<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OgmNotificationType extends Model
{
    /** @use HasFactory<\Database\Factories\OgmNotificationTypeFactory> */
    protected $fillable = [
        'name', 'icon',
        'title_en', 'title_ar', 'title_fr',
        'content_en', 'content_ar', 'content_fr'
    ];
    
    public function notifications() { return $this->hasMany(OgmNotification::class, 'notification_type_id'); }
}
