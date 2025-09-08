<?php

namespace App\Http\Controllers\MediaForce;

use App\Http\Controllers\Controller;
use App\Models\MediaForceVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class MediaVideoController extends Controller
{
    public function index()
    {
        $mf = Auth::user();
        $videos = $mf->videos()->orderBy('slot_number')->get();
        return Inertia::render('media-forces/videos/index', ['videos' => $videos]);
    }

    public function show(int $slot)
    {
        $mf = Auth::user();
        $video = $mf->videos()->where('slot_number', $slot)->firstOrFail();
        return Inertia::render('media-forces/videos/show', ['video' => $video]);
    }

    /** Direct upload endpoint (returns stored path + URL) */
    public function upload(Request $req)
    {
        $req->validate([
            'file' => ['required','file','mimetypes:video/mp4,video/quicktime,video/x-matroska,video/webm','max:512000'], // ~500MB
        ]);

        $user = Auth::user(); // authenticated media_force
        $disk = config('filesystems.default', 'public'); // use s3/public as you like

        // Store under /media_forces/{id}/YYYY/MM/
        $dir = 'media_forces/'.$user->id.'/'.now()->format('Y/m');
        $path = $req->file('file')->store($dir, $disk);

        return response()->json([
            'disk'       => $disk,
            'file_path'  => $path,
            'public_url' => Storage::disk($disk)->url($path),
        ]);
    }

    /** Persist DB row after upload is done and user clicks Done */
    public function store(Request $req)
    {
        $req->validate([
            'slot_number'     => ['required','integer','min:1','max:11'],
            'title'           => ['nullable','string','max:255'],
            'description'     => ['nullable','string'],
            'file_path'       => ['required','string'],
            'status'          => ['nullable', Rule::in(['draft','submitted'])], // usually submitted on Done
        ]);

        $user = Auth::user();

        // Ensure this path is indeed under the user's folder (basic guard-rail)
        if (!str_starts_with($req->file_path, 'media_forces/'.$user->id.'/')) {
            return response()->json(['message' => 'Invalid file ownership.'], 422);
        }

        // Upsert by unique (media_force_id, slot_number)
        $video = MediaForceVideo::updateOrCreate(
            ['media_force_id' => $user->id, 'slot_number' => $req->integer('slot_number')],
            [
                'title'            => $req->input('title'),
                'description'      => $req->input('description'),
                'file_path'        => $req->input('file_path'),
                // you can fill these later with a Job (ffmpeg) that inspects file:
                // 'thumbnail_path' => ...,
                // 'duration_seconds' => ...,
                'status'           => $req->input('status', 'submitted'),
                'submitted_at'     => now(),
                'reviewed_at'      => null,
                'reviewer_id'      => null,
                'review_feedback'  => null,
            ]
        );

        return response()->json([
            'ok'    => true,
            'video' => $video,
        ]);
    }

}
