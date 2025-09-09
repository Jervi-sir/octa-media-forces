<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PushNotificationToken extends Model
{
    /** @use HasFactory<\Database\Factories\PushNotificationTokenFactory> */
  protected $fillable = [
    'owner_type',
    'owner_id',
    'platform',
    'expo_push_token',
    'device_token',
    'device_id',
    'device_model',
    'os_version',
    'app_version',
    'locale',
    'is_active',
    'last_seen_at',
  ];

  protected $casts = [
    'is_active'    => 'boolean',
    'last_seen_at' => 'datetime',
  ];

  public function owner()
  {
    return $this->morphTo();
  }
}
