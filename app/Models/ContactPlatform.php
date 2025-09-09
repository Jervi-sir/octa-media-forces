<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactPlatform extends Model
{
    /** @use HasFactory<\Database\Factories\ContactPlatformFactory> */
    use HasFactory;

    protected $fillable = ['name', 'code_name', 'icon_url', 'icon_svg', 'description'];

    public function os()
    {
        return $this->belongsToMany(Os::class, 'os_contact_lists', 'contact_platforms_id', 'os_id')
                    ->withPivot('link')
                    ->withTimestamps();
    }

    public function osContactLists()
    {
        return $this->hasMany(OsContactList::class, 'contact_platforms_id');
    }
}
