<?php

namespace App\Http\Controllers\Api\v2_9\OGM;

use App\Helpers\v2_9\OGMHelpers;
use App\Http\Controllers\Controller;
use App\Models\Draft;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class M3DraftController extends Controller
{
    public function list(Request $request)
    {
        Validator::make($request->all(), [
            'os_id' => 'required|exists:os,id',
        ]);

        $perPage = $request->query('per_page', 10);
        $drafts = Draft::where('ogm_id', $request->user()->id)
            ->where('os_id', $request->os_id)
            ->latest()
            ->paginate($perPage);

        $data['drafts'] = [];
        foreach ($drafts as $key => $draft) {
            $data['drafts'][$key] = [
                'id' => $draft->id,
                'image_url' => OGMHelpers::GenerateImageUrl($draft->image_url),
            ];
        }

        return response()->json([
            'data' => $data['drafts'],
            'next_page' => $drafts->hasMorePages() ? $drafts->currentPage() + 1 : null,
            'per_page' => (int) $perPage,
        ]);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'os_id' => 'required|exists:os,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ogm_id = $request->user()->id;
        // Security: Ensure the ogm_id matches the authenticated user
        if ($ogm_id !== $request->user()->id) {
            return response()->json(['errors' => ['ogm_id' => 'Unauthorized ogm_id']], 403);
        }

        $drafts = collect();
        try {
            foreach ($request->file('images') as $image) {
                $path = $image->store('drafts', 'public'); // Store in storage/public/drafts
                if (!$path) {
                    Log::error('Failed to store image', ['ogm_id' => $ogm_id]);
                    throw new \Exception('Failed to store image');
                }

                $draft = Draft::create([
                    'ogm_id' => $ogm_id,
                    'os_id' => $request->os_id,
                    'image_url' => Storage::url($path),
                ]);
                $drafts->push($draft);
            }
        } catch (\Exception $e) {
            Log::error('Draft creation failed', ['error' => $e->getMessage()]);
            return response()->json(['errors' => ['images' => 'Failed to process images']], 500);
        }

        return response()->json([
            'message' => 'Drafts created',
            'drafts' => $drafts->map(function ($draft) {
                return OGMHelpers::DraftFormatter($draft);
            }),
        ], 201);
    }

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $drafts = Draft::whereIn('id', $request->ids)
            ->where('ogm_id', $request->user()->id) // Security: only delete user's drafts
            ->get();

        try {
            foreach ($drafts as $draft) {
                $filePath = str_replace(Storage::url(''), '', $draft->image_url);
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
                $draft->delete();
            }
        } catch (\Exception $e) {
            Log::error('Draft deletion failed', ['error' => $e->getMessage(), 'ids' => $request->ids]);
            return response()->json(['errors' => ['drafts' => 'Failed to delete drafts']], 500);
        }

        return response()->json([
            'message' => 'Drafts deleted'
        ]);
    }
}
