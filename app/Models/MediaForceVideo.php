<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class MediaForceVideo extends Model
{
    protected $fillable = [
        'media_force_id',
        'slot_number',
        'title',
        'description',
        'file_path',
        'thumbnail_path',
        'duration_seconds',
        'status',
        'submitted_at',
        'reviewed_at',
        'reviewer_id',
        'review_feedback'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at'  => 'datetime',
    ];

    // protected $appends = ['file_url', 'thumbnail_url'];

    public function mediaForce()
    {
        return $this->belongsTo(MediaForce::class);
    }
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function scopeForReview($q)
    {
        return $q->whereIn('status', ['submitted']);
    }
    public function getFilePathAttribute($value): ?string
    {
        return $value ? url(Storage::url($value)) : null;
    }
    public function getThumbnailPathAttribute($value): ?string
    {
        return $value ? url(Storage::url($value)) : null;
    }

}
