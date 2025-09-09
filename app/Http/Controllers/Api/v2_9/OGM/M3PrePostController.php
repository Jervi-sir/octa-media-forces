<?php

namespace App\Http\Controllers\Api\v2_9\OGM;

use App\Helpers\v2_9\OGMHelpers;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Draft;
use App\Models\Gender;
use App\Models\OgmQualificationProgress;
use App\Models\Product;
use App\Models\ProductGender;
use App\Models\ProductSize;
use App\Models\ProductTag;
use App\Models\Size;
use App\Models\Status;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class M3PrePostController extends Controller
{
    public function list(Request $request)
    {
        $status = Status::where('name', 'pre-post')->first();
        // Fetch pre-posts with pagination
        $perPage = $request->query('per_page', 10); // Default to 10 items per page
        $prePosts = Product::where('status_id', $status->id)
            ->where('os_id', $request->os_id)
            ->select('id', 'images')
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        // Transform the response to include nb_images
        $prePosts->getCollection()->transform(function ($prePost) {
            // $images = $prePost->images ?? [];
            return OGMHelpers::ProductPreview($prePost);
            // return [
            //     'id' => $prePost->id,
            //     'thumbnail' => OGMHelpers::GenerateImageUrl($images[0]),
            //     'nb_images' => count($images),
            // ];
        });

        return response()->json([
            'data' => $prePosts->items(),
            'next_page' => $prePosts->hasMorePages() ? $prePosts->currentPage() + 1 : null,
            'total' => $prePosts->total(),
        ]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'draft_ids' => 'required|array',
            'draft_ids.*' => 'exists:drafts,id',
            'os_id' => 'required|exists:os,id',
        ]);

        // Fetch drafts by IDs
        $drafts = Draft::whereIn('id', $request->draft_ids)->get();

        // Check if all provided draft_ids exist
        if ($drafts->count() !== count($request->draft_ids)) {
            return response()->json([
                'message' => 'One or more drafts not found',
            ], 404);
        }
        // Extract image URLs from drafts
        $imageUrls = $drafts->pluck('image_url')->toArray();

        // Create the product
        $product = Product::create([
            'os_id' => $request->os_id,
            'images' => $imageUrls, // Laravel automatically encodes arrays as JSON
            'status_id' => Status::where('name', 'pre-post')->first()->id,
        ]);

        // Delete the drafts
        Draft::whereIn('id', $request->draft_ids)->delete();

        return response()->json([
            'message' => 'Pre-Post created successfully',
            'product' => OGMHelpers::ProductPreview($product),
        ], 201);
    }

    public function show($product_id)
    {
        try {
            $product = Product::with([
                'category.sizes',
                'genders',
                'sizes.category',
                'tags'
            ])
                ->where('id', $product_id)
                // ->where('status', 'pre-post')
                ->firstOrFail();

            $response = OGMHelpers::ProductFormat($product, ['category', 'sizes', 'genders', 'tags']);

            // Instantiate HelperController and call getGendersCategoriesSizes
            $helperController = new HelperController();
            $helperData = $helperController->getGendersCategoriesSizes()->getData(true);
            $response['helper'] = $helperData;
            $suggested_tags = $helperController->getTrendingTags()->getData(true);
            $response['suggested_tags'] = $suggested_tags;

            return response()->json($response, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Product not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch product'], 500);
        }
    }

    public function update(Request $request, $product_id)
    {
        // Find the product
        $product = Product::where('id', $product_id)
            // ->where('sta tus', 'pre-post')
            ->firstOrFail();

        // Validate the request
        $validated = $request->validate([
            'images' => 'nullable|array',
            'images.*' => 'string', // Ensure each image is a string (URL or path)
            'category_id' => 'nullable|exists:categories,id',
            'genders' => 'nullable|array',
            'genders.*' => 'exists:genders,id', // Validate each gender ID
            'sizes' => 'nullable|array',
            'sizes.*' => 'exists:sizes,id', // Validate each size ID
            'tags' => 'nullable|array',
            // 'tags.*' => 'exists:tags,id', // Validate each tag ID
            'price' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100', // Assuming discount is a percentage
            'discount_end_date' => 'nullable|date|after:now', // Ensure future date if provided
            'description' => 'nullable|string|max:1000', // Add max length for description
            'status' => 'required|string'
        ]);

        // Strip base URL from images if necessary
        $baseUrl = config('app.url'); // e.g., http://192.168.1.100:8000/
        $imagePaths = array_map(function ($image) use ($baseUrl) {
            return str_replace($baseUrl . '/storage/', 'storage/', $image); // Normalize to storage path
        }, $request->images ?? []);

        $product->update([
            'images' => $imagePaths,
            'category_id' => $request->category_id,
            'price' => $request->price,
            'discount' => $request->discount,
            'discount_end_date' => $request->discount_end_date,
            'description' => $request->description,
            'status_id' => Status::where('name', $request->status)->first()->id,
        ]);

        $product->genders()->sync($request->genders ?? []);
        $product->sizes()->sync($request->sizes ?? []);
        $product->tags()->sync($request->tags ?? []); // Sync only valid tag IDs

        // $suggestedTags = Tag::where('suggested', true)
        //     ->get()
        //     ->groupBy('category')
        //     ->map(function ($tags, $category) {
        //         return [
        //             'category' => $category,
        //             'tags' => $tags->map(fn($tag) => ['id' => $tag->id, 'name' => $tag->name])->values()->toArray(),
        //         ];
        //     })->values()->toArray();

        // if the ogm is not approved yet then means he is in the qualification
        $ogm = $request->user();
        if ($ogm->is_approved === false) {
            // check the total of products
            $published_status = Status::where('name', 'published')->first();
            $count = Product::where('os_id', $product->os_id)
                ->where('status_id', $published_status->id)
                ->count();
            // check the progress status of the qualification
            $ogm_progress = OgmQualificationProgress::where('ogm_id', $ogm->id)->first();
            $progress = $ogm_progress->progress; // Get the entire progress array

            if ($progress['ogm_qualification']['type'] === 'start' || $progress['ogm_qualification']['type'] === 'failed') {
                if ($count >= OGMHelpers::$max_published_for_approval) {
                    // Update the specific key in the progress array
                    $progress['ogm_qualification'] = [
                        'type' => 'pending',
                    ];
                    // Assign the updated array back to the progress property
                    $ogm_progress->progress = $progress;
                }
            }

            $ogm_progress->save();
        }

        return response()->json([
            'message' => 'Product updated successfully',
            // 'product' => $product->load([
            //     'category.sizes',
            //     'genders',
            //     'sizes.category',
            //     'tags',
            // ]),
            'product' => OGMHelpers::ProductPreview($product),
            // 'suggested_tags' => $suggestedTags,
        ], 200);
    }

    public function refresh($product_id, Request $request)
    {
        $product = Product::findOrFail($product_id);
        $publishedStatus = Status::where('name', 'published')->firstOrFail();
        if ($product->status_id !== $publishedStatus->id) {
            return response()->json(['error' => 'Only published products can be refreshed'], 422);
        }
        $validator = Validator::make($request->all(), [
            'disappear_at' => 'sometimes|date|after:now',
            'duration_hours' => 'sometimes|numeric|min:1', // Optional duration in hours
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $newDisappearAt = $request->disappear_at
            ? Carbon::parse($request->disappear_at)
            : ($request->duration_hours
                ? Carbon::now()->addHours($request->duration_hours)
                : Carbon::now()->addDays(30)); // Default: extend by 30 days
        $product->update([
            'disappear_at' => $newDisappearAt,
        ]);
        // $product->load(['os', 'category', 'sizes', 'genders', 'tags']);
        // return response()->json(Product::ProductFormat($product, ['os', 'category', 'sizes', 'genders', 'tags']));
        return response()->json([
            'message' => 'Refreshed successfully',
            'product' => OGMHelpers::ProductPreview($product),
        ]);
    }


    public function delete($product_id)
    {
        try {
            $product = Product::where('id', $product_id)
                ->firstOrFail();

            // Detach relationships (genders, sizes, tags)
            $product->genders()->detach();
            $product->sizes()->detach();
            $product->tags()->detach();

            // Delete the product
            $product->delete();

            return response()->json([
                'message' => 'Product deleted successfully',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Product not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete product'], 500);
        }
    }

    public function publish($product_id, Request $request) {}
}
