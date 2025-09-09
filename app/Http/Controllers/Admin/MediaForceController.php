<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaForce;
use App\Models\MediaForceVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaForceController extends Controller
{
    public function index(Request $request)
    {
        $forces = MediaForce::query()
            ->withCount([
                'videos as videos_total',
                'videos as videos_submitted_count' => fn($q) => $q->where('status', 'submitted'),
                'videos as videos_approved_count'  => fn($q) => $q->where('status', 'approved'),
            ])
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/media-forces/list', [
            'forces' => $forces,
        ]);
    }

    public function show(MediaForce $mediaForce)
    {
        $mediaForce->load([
            'videos' => fn($q) => $q->orderBy('slot_number'),
        ]);

        return Inertia::render('admin/media-forces/show', [
            'force'  => [
                'id'    => $mediaForce->id,
                'name'  => $mediaForce->name,
                'email' => $mediaForce->email,
            ],
            'videos' => $mediaForce->videos->map(function ($v) {
                return [
                    'id'                => $v->id,
                    'slot_number'       => $v->slot_number,
                    'title'             => $v->title,
                    'description'       => $v->description,
                    'file_path'         => $v->file_path ? $v->file_path : null,
                    'thumbnail_path'    => $v->thumbnail_path ? $v->thumbnail_path : null,
                    'duration_seconds'  => $v->duration_seconds,
                    'status'            => $v->status,
                    'review_feedback'   => $v->review_feedback,
                    'submitted_at'      => optional($v->submitted_at)->toDateTimeString(),
                    'reviewed_at'       => optional($v->reviewed_at)->toDateTimeString(),
                ];
            }),
            'statusOptions' => ['draft', 'submitted', 'approved', 'changes_requested', 'rejected'],
        ]);
    }

    public function update(Request $request, MediaForceVideo $mediaForceVideo)
    {
        $data = $request->validate([
            'status'          => ['required', Rule::in(['draft', 'submitted', 'approved', 'changes_requested', 'rejected'])],
            'review_feedback' => ['nullable', 'string'],
        ]);

        MediaForceVideo::whereKey($mediaForceVideo->getKey())->update([
            'status'          => $data['status'],
            'review_feedback' => $data['review_feedback'] ?? null,
            'reviewer_id'     => Auth::id(),
            'reviewed_at'     => now(),
            'updated_at'      => now(),
        ]);

        $mediaForceVideo->refresh();

        // If the request wants JSON (axios or fetch), return JSON
        if ($request->wantsJson()) {
            return response()->json([
                'ok'    => true,
                'video' => [
                    'id'               => $mediaForceVideo->id,
                    'slot_number'      => $mediaForceVideo->slot_number,
                    'title'            => $mediaForceVideo->title,
                    'description'      => $mediaForceVideo->description,
                    'file_path'        => $mediaForceVideo->file_path ? Storage::url($mediaForceVideo->file_path) : null,
                    'thumbnail_path'   => $mediaForceVideo->thumbnail_path ? Storage::url($mediaForceVideo->thumbnail_path) : null,
                    'duration_seconds' => $mediaForceVideo->duration_seconds,
                    'status'           => $mediaForceVideo->status,
                    'review_feedback'  => $mediaForceVideo->review_feedback,
                    'submitted_at'     => optional($mediaForceVideo->submitted_at)->toDateTimeString(),
                    'reviewed_at'      => optional($mediaForceVideo->reviewed_at)->toDateTimeString(),
                ],
            ]);
        }

        return back()->with('success', 'Video updated.');
    }


    public function stream(Request $req, MediaForceVideo $mediaForceVideo)
    {
        $diskName = config('filesystems.default', 'public');
        $disk = Storage::disk($diskName);

        // 1) Normalize to a disk-relative path
        $raw = $mediaForceVideo->file_path ?? '';
        if (Str::startsWith($raw, ['http://', 'https://'])) {
            // Expect URLs like /storage/media_forces/5/...  -> strip the /storage/ prefix
            $pathPart = parse_url($raw, PHP_URL_PATH) ?: '';
            $path = ltrim(Str::after($pathPart, '/storage/'), '/');  // => media_forces/5/...
        } else {
            // Already a relative path
            $path = ltrim($raw, '/');                                 // => media_forces/5/...
        }

        abort_unless($path && $disk->exists($path), 404, 'Video not found.');

        // 2) Resolve full local path (only works for local-like disks)
        $full = $disk->path($path);   // storage/app/public/media_forces/5/...
        $size = @filesize($full);
        abort_unless($size !== false, 404, 'Video not found.');

        // 3) Detect mime
        $mime = $disk->mimeType($path) ?? (mime_content_type($full) ?: 'video/mp4');

        // 4) Parse Range
        $range = $req->header('Range');   // e.g., "bytes=0-"
        $start = 0;
        $end = $size - 1;
        $status = 200;

        if ($range && preg_match('/bytes=(\d*)-(\d*)/', $range, $m)) {
            if ($m[1] !== '') $start = max(0, (int) $m[1]);
            if ($m[2] !== '') $end   = min((int) $m[2], $size - 1);
            if ($start > $end) {
                return response('', 416, ['Content-Range' => "bytes */{$size}"]);
            }
            $status = 206;
        }

        $length = $end - $start + 1;

        $headers = [
            'Content-Type'   => $mime,
            'Accept-Ranges'  => 'bytes',
            'Cache-Control'  => 'private, max-age=0, must-revalidate',
            'Content-Length' => (string) $length,
        ];
        if ($status === 206) {
            $headers['Content-Range'] = "bytes {$start}-{$end}/{$size}";
        }

        return new StreamedResponse(function () use ($full, $start, $length) {
            $chunk = 1024 * 1024; // 1 MB
            $fh = fopen($full, 'rb');
            fseek($fh, $start);
            $remaining = $length;
            while ($remaining > 0 && !feof($fh)) {
                $read = ($remaining > $chunk) ? $chunk : $remaining;
                echo fread($fh, $read);
                flush();
                $remaining -= $read;
            }
            fclose($fh);
        }, $status, $headers);
    }
}
