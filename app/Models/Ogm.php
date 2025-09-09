<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Kra8\Snowflake\HasSnowflakePrimary;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Ogm extends Model
{
    /** @use HasFactory<\Database\Factories\OgmFactory> */
    use HasSnowflakePrimary, HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'displayed_id', 'wilaya_id', 'full_name', 'username', 'password', 'password_plain_text', 'phone_number', 'image',
    ];

    protected $hidden = [
        'password', 'password_plain_text',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function wilaya()
    {
        return $this->belongsTo(Wilaya::class);
    }

    public function stores()
    {
        return $this->hasMany(Os::class);
    }

    // public function qualificationPackagingTest() { return $this->hasOne(QualificationOperation3::class); }

}
