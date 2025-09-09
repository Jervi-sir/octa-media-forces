<?php

namespace App\Http\Controllers\Api\v2_9\OGM;

use App\Helpers\v2_9\OGMHelpers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MediaController extends Controller
{
    public function uploadImage(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'image' => 'required', // Max 2MB
            'target' => 'nullable'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Handle the image upload
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('products', 'public');
                $imageUrl = Storage::url($path);

                return response()->json([
                    'message' => 'Image uploaded successfully',
                    'image_url' => OGMHelpers::GenerateImageUrl($imageUrl),
                ], 200);
            }

            return response()->json([
                'message' => 'No image provided',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to upload image',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
