<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class MediaForce extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name','email','password','password_plain_text'];
    protected $hidden   = ['password','remember_token'];

    public function videos() {
        return $this->hasMany(MediaForceVideo::class);
    }

    // Helper: ensure all 11 slots exist (optional)
    public function ensureSlots(int $total = 11): void
    {
        $existing = $this->videos()->pluck('slot_number')->all();
        $missing  = array_diff(range(1, $total), $existing);

        if (!empty($missing)) {
            $this->videos()->insert(array_map(fn ($n) => [
                'slot_number' => $n,
                'status'      => 'draft',
                'created_at'  => now(),
                'updated_at'  => now(),
            ], $missing));
        }
    }
}
