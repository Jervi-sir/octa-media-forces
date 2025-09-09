<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    /** @use HasFactory<\Database\Factories\TagFactory> */
    protected $fillable = ['category_id', 'name', 'en', 'ar', 'fr'];

    protected $casts = [
        'id' => 'string',
    ];

    public function category()
    {
        return $this->belongsTo(TagCategory::class, 'tag_category_id');
    }

    public function tagCategory()   { return $this->belongsTo(TagCategory::class); }
    public function productTags()   { return $this->hasMany(ProductTag::class); }
    public function products()      { return $this->belongsToMany(Product::class, 'product_tags'); }
    public function tagNetworks()   { return $this->hasMany(TagNetwork::class); }
}
