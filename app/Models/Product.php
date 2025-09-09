<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasSnowflakePrimary;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasSnowflakePrimary;

    protected $fillable = ['id', 'os_id', 'images', 'price', 'discount', 'discount_end_date', 'description', 'category_id', 'status_id', 'posted_at',  'disappear_at'];

    protected $casts = [
        'id' => 'string',
        'images' => 'array',
        'discount_end_date' => 'datetime',
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'posted_at' => 'datetime',
        // 'disappear_at' => 'datetime',
    ];
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }


    public function sizes()
    {
        return $this->belongsToMany(Size::class, 'product_sizes', 'product_id', 'size_id');
    }

    public function genders()
    {
        return $this->belongsToMany(Gender::class, 'product_genders', 'product_id', 'gender_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'product_tags', 'product_id', 'tag_id');
    }

    public function os()
    {
        return $this->belongsTo(Os::class);
    }

    public function savePosts()
    {
        return $this->hasMany(SavePost::class);
    }

    public function buyings()
    {
        return $this->hasMany(OpBuying::class);
    }

}
