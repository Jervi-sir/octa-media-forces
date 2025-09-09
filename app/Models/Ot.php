<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Ot extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\OtFactory> */
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'username', 'password', 'password_plain_text',
    ];

    protected $hidden = [
        'password', 'password_plain_text',
    ];
}
