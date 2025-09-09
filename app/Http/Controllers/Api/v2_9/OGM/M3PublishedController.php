<?php

namespace App\Http\Controllers\Api\v2_9\OGM;

use App\Helpers\v2_9\OGMHelpers;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Status;
use Illuminate\Http\Request;

class M3PublishedController extends Controller
{
    public function list(Request $request)
    {
        $status = Status::where('name', 'published')->first();
        // Fetch pre-posts with pagination
        $perPage = $request->query('per_page', 10); // Default to 10 items per page
        $posts = Product::where('status_id', $status->id)
            ->where('os_id', $request->os_id)
            ->select('id', 'images', 'disappear_at')
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        // Transform the response to include nb_images
        $posts->getCollection()->transform(function ($post) {
            // $images = $post->images ?? [];
            return OGMHelpers::ProductPreview($post);
            // return [
            //     'id' => $post->id,
            //     'thumbnail' => OGMHelpers::GenerateImageUrl($images[0]),
            //     'disappear_at' => $post->disappear_at,
            // ];
        });

        return response()->json([
            'data' => $posts->items(),
            'next_page' => $posts->hasMorePages() ? $posts->currentPage() + 1 : null,
            'total' => $posts->total(),
            'total' => $posts,
        ]);
    }
    public function update($product_id, Request $request) {}
    public function repost($product_id, Request $request) {}

}




