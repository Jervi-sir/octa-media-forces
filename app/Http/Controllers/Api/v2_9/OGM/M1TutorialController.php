<?php

namespace App\Http\Controllers\Api\v2_9\OGM;

use App\Http\Controllers\Controller;
use App\Models\OgmTutorial;
use Illuminate\Http\Request;

class M1TutorialController extends Controller
{
    public function listTutorials(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 10); // Default to 10 items per page
            $tutorials = OgmTutorial::select('id', 'video_url', 'title', 'posted_at')
                ->orderBy('posted_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $tutorials->items(),
                'next_page' => $tutorials->currentPage() < $tutorials->lastPage() ? $tutorials->currentPage() + 1 : null,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tutorials: ' . $e->getMessage(),
            ], 500);
        }
    }
}
