<?php

namespace App\Http\Controllers\MediaForce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MediaDashboardController extends Controller
{
    public function index()
    {
        $mf = Auth::guard('media_forces')->user();
        $mf->ensureSlots();

        $videos = $mf->videos()
            ->orderBy('slot_number')
            ->get([
                'id','slot_number','title','description','file_path',
                'status','submitted_at','reviewed_at','review_feedback'
            ])
            ->map(fn ($v) => [
                'id'           => $v->id,
                'slot_number'  => $v->slot_number,
                'title'        => $v->title,
                'description'  => $v->description,
                'file_path'    => $v->file_path,
                'public_url'   => $v->public_url,   // accessor
                'status'       => $v->status,
                'review_feedback' => $v->review_feedback,
                'submitted_at' => optional($v->submitted_at)->toIso8601String(),
                'reviewed_at'  => optional($v->reviewed_at)->toIso8601String(),
                'feedback'     => $v->review_feedback,
            ]);

        return Inertia::render('media-forces/dashboard/index', [
            'mediaForce' => $mf->only('id','name','email'),
            'videos'     => $videos,
        ]);
    }
}
