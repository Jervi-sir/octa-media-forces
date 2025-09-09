<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasSnowflakePrimary;

class NotificationToken extends Model
{
    /** @use HasFactory<\Database\Factories\NotificationTokenFactory> */
    use HasFactory, HasSnowflakePrimary;
    
    public function user()
    {
        switch ($this->user_type) {
            case 'ogm':
                return $this->belongsTo(Op::class, 'user_id');
            case 'op':
                return $this->belongsTo(Op::class, 'user_id');
            case 'os':
                return $this->belongsTo(Os::class, 'user_id');
            case 'ot':
                return $this->belongsTo(Ot::class, 'user_id');
            case 'user':
                return $this->belongsTo(User::class, 'user_id');
            default:
                return null;
        }
    }
}
