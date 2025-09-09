<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasSnowflakePrimary;

class OsContactList extends Model
{
    /** @use HasFactory<\Database\Factories\OsContactListFactory> */
    use HasSnowflakePrimary;
    protected $fillable = ['os_id', 'contact_platforms_id', 'link'];

    protected $casts = [
        'id' => 'string',
        'os_id' => 'string',
    ];

    public function os()
    {
        return $this->belongsTo(Os::class, 'os_id');
    }

    public function contactPlatform()
    {
        return $this->belongsTo(ContactPlatform::class, 'contact_platforms_id');
    }

    public static function ContactFormat($platform)
    {
        return [
            'id' => $platform->id,
            'os_id' => $platform->os_id,
            'link' => $platform->link,
            'contact_platforms_id' => $platform->contact_platforms_id,
            // 'platform' => $platform->contactPlatform,
            // Alternatively, use the nested structure if preferred:
            'platform' => [
                'id' => $platform->contactPlatform->id,
                'name' => $platform->contactPlatform->name,
                'code_name' => $platform->contactPlatform->code_name,
                // 'icon_svg' => $platform->contactPlatform->icon_svg,
                // 'icon_url' => $platform->contactPlatform->icon_url,
            ],
            'created_at' => $platform->created_at,
            'updated_at' => $platform->updated_at,
        ];
    }

}
