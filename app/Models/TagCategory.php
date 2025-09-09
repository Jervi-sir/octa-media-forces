<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TagCategory extends Model
{
    /** @use HasFactory<\Database\Factories\TagCategoryFactory> */
    protected $fillable = ['name'];

    public function tags() {
        return $this->hasMany(Tag::class);
    }
}
