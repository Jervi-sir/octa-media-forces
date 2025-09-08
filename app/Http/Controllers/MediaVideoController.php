<?php

namespace App\Http\Controllers;

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
        return Inertia::render('media/videos/index', ['videos' => $videos]);
    }

    public function show(int $slot)
    {
        $mf = Auth::user();
        $video = $mf->videos()->where('slot_number', $slot)->firstOrFail();
        return Inertia::render('media/videos/show', ['video' => $video]);
    }

    // public function store(Request $r, int $slot)
    // {
    //     $mf = Auth::user();
    //     $data = $r->validate([
    //         'title' => 'nullable|string|max:120',
    //         'description' => 'nullable|string|max:2000',
    //         'file' => 'nullable|file|mimetypes:video/mp4,video/quicktime,video/x-matroska|max:512000', // ~500MB
    //         'thumbnail' => 'nullable|image|max:5120',
    //         'duration_seconds' => 'nullable|integer|min:0',
    //     ]);

    //     $video = $mf->videos()->firstOrCreate(['slot_number' => $slot], []);

    //     if ($r->hasFile('file')) {
    //         if ($video->file_path) Storage::disk('public')->delete($video->file_path);
    //         $data['file_path'] = $r->file('file')->store("media/{$mf->id}/videos", 'public');
    //         // reset status back to draft on new upload if previously approved?
    //         if (in_array($video->status, ['approved', 'rejected', 'changes_requested'])) {
    //             $data['status'] = 'draft';
    //             $data['submitted_at'] = null;
    //         }
    //     }
    //     if ($r->hasFile('thumbnail')) {
    //         if ($video->thumbnail_path) Storage::disk('public')->delete($video->thumbnail_path);
    //         $data['thumbnail_path'] = $r->file('thumbnail')->store("media/{$mf->id}/thumbs", 'public');
    //     }

    //     $video->update($data);
    //     return back()->with('success', 'Saved.');
    // }

    // public function submit(Request $r, int $slot)
    // {
    //     $mf = Auth::user();
    //     $video = $mf->videos()->where('slot_number', $slot)->firstOrFail();
    //     if (!$video->file_path) return back()->withErrors(['file' => 'Upload a video before submitting.']);
    //     $video->update(['status' => 'submitted', 'submitted_at' => now(), 'review_feedback' => null, 'reviewer_id' => null]);
    //     return back()->with('success', 'Submitted for review.');
    // }

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
