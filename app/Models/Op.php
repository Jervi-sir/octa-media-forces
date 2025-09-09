<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Kra8\Snowflake\HasSnowflakePrimary;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Op extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\OpFactory> */
    use HasSnowflakePrimary, HasApiTokens, Notifiable;

    protected $guarded = [];
    protected $fillable = [
        'username', 'full_name', 'password', 'password_plain_text', 'email', 'phone_number', 'image', 'wilaya_id',
    ];

    protected $hidden = [
        'password', 'password_plain_text',
    ];


    public function wilaya()         { return $this->belongsTo(Wilaya::class); }
    public function contactLists()   { return $this->hasMany(OpContactList::class); }
    public function savePosts()      { return $this->hasMany(SavePost::class); }
    public function buyings()        { return $this->hasMany(OpBuying::class); }
    public function collections()    { return $this->hasMany(OpCollection::class); }
    public function shippingTickets(){ return $this->hasMany(OpShippingTicket::class); }
    public function shippingAddresses(){ return $this->hasMany(OpShippingAddress::class); }
    public function tutorials()      { return $this->hasMany(OgmTutorial::class, 'ogm_id'); }
    public function logsData()       { return $this->hasMany(OgmLogsData::class, 'ogm_id'); }
    public function payments()       { return $this->hasMany(OgmPayment::class, 'ogm_id'); }

}
