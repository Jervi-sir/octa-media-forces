<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasSnowflakePrimary;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Os extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\OFactory> */
    use HasSnowflakePrimary, HasApiTokens, Notifiable;
    protected $fillable = [
        'ogm_id', 'wilaya_id', 'store_name', 'image', 'phone_number', 'map_link', 'bio',
        'is_approved', 'is_blocked'
    ];

    protected $casts = [
        'id' => 'string',
        'ogm_id' => 'string',
    ];

    public function ogm()       { return $this->belongsTo(Ogm::class); }
    public function wilaya()    { return $this->belongsTo(Wilaya::class); }
    public function products()  { return $this->hasMany(Product::class); }
    public function collections(){ return $this->belongsToMany(OpCollection::class, 'op_collections_os'); }
    public function contactLists() { return $this->hasMany(OsContactList::class, 'os_id'); }
    public function contactPlatforms()
    {
        return $this->belongsToMany(ContactPlatform::class, 'os_contact_lists', 'os_id', 'contact_platforms_id')
                    ->withPivot('link')
                    ->withTimestamps();
    }

}
