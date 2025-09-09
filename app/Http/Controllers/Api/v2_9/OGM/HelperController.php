<?php

namespace App\Http\Controllers\Api\v2_9\OGM;

use App\Helpers\v2_9\OGMHelpers;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Gender;
use App\Models\Tag;
use App\Models\TagCategory;
use App\Models\Wilaya;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class HelperController extends Controller
{
    public function listData(Request $request)
    {
        $allowed = [
            'wilayas',
            'platforms',
            'genders',
            'categories',
            'categories',
            'categories_sizes',
            'trending_tags',
        ];
        // Validate includes (GET /helpers/bootstrap?include[]=wilayas&include[]=platforms)
        $validated = $request->validate([
            'include'   => ['array'],
            'include.*' => [Rule::in($allowed)],
        ]);
        // If no includes provided -> return ALL
        $includes = collect($validated['include'] ?? $allowed)->unique()->values()->all();
        $response = [];

        $ttl = now()->addHours(12); // cache TTL
        // wilayas
        if (in_array('wilayas', $includes, true)) {
            $response['wilayas'] = Wilaya::select('id', 'name', 'number', 'number_text', 'en', 'ar', 'fr')
                ->get()
                ->map(fn($w) => OGMHelpers::WilayaFormat($w))
                ->values();
        }
        // platforms
        if (in_array('platforms', $includes, true)) {
            $response['platforms'] = OGMHelpers::GetPlatforms();
        }
        // genders
        if (in_array('genders', $includes, true)) {
            $response['genders'] = Gender::select('id', 'name')->get();
        }
        // categories (no sizes)
        if (in_array('categories', $includes, true)) {
            $response['categories'] = Category::select('id', 'name', 'en', 'ar', 'fr')->get();
        }
        // categories_sizes (sizes grouped by category) — shape:
        // [{ "category_id": 1, "sizes": [{id,name}, ...] }, ...]
        if (in_array('categories_sizes', $includes, true)) {
            // If you have a Size model with category_id, you could do a single query + groupBy.
            // Since you already use relation `sizes`, we'll leverage it but only pull what we need.
            $response['categories_sizes'] = Category::with(['sizes:id,name,category_id'])
                ->select('id') // keep payload minimal
                ->get()
                ->map(fn($c) => [
                    'category_id' => $c->id,
                    'sizes' => $c->sizes->map(fn($s) => [
                        'id' => $s->id,
                        'name' => $s->name,
                    ])->values(),
                ])->values();
        }

        // trending_tags
        if (in_array('trending_tags', $includes, true)) {
            $response['trending_tags'] = TagCategory::with(['tags:id,tag_category_id,name'])
                ->get()
                ->map(fn($cat) => [
                    'category' => $cat->name,
                    'tags' => $cat->tags->map(fn($t) => [
                        'id' => $t->id,
                        'name' => $t->name,
                    ])->values(),
                ])->values();
        }

        return response()->json($response);
    }

    public function getWilayas()
    {
        $wilayas = Wilaya::all()->map(fn($wilaya) => OGMHelpers::WilayaFormat($wilaya));

        return response()->json($wilayas);
    }

    public function getGendersCategoriesSizes()
    {
        $categoriesWithSizes = Category::with('sizes')->get()->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'en' => $category->en,
                'ar' => $category->ar,
                'fr' => $category->fr,
                'sizes' => $category->sizes->map(function ($size) {
                    return [
                        'id' => $size->id,
                        'name' => $size->name,
                    ];
                })->toArray(),
            ];
        });

        return response()->json([
            'categories' => $categoriesWithSizes,
            'genders' => Gender::all()->map(function ($gender) {
                return [
                    'id' => $gender->id,
                    'name' => $gender->name,
                ];
            }),
        ]);
    }

    public function getTrendingTags()
    {
        $categories = TagCategory::with(['tags:id,tag_category_id,name'])->get();
        $result = $categories->map(function ($category) {
            return [
                'category' => $category->name,
                'tags' => $category->tags->map(function ($tag) {
                    return [
                        'id' => $tag->id,
                        'name' => $tag->name,
                    ];
                })
            ];
        });

        return response()->json($result);
    }


    public function searchTags(Request $request)
    {
        $query = $request->query('q', ''); // Get search query from ?q= parameter

        // Search tags by name, en, ar, or fr using LIKE
        $tags = Tag::when($query, function ($queryBuilder) use ($query) {
            $queryBuilder->where('name', 'ilike', "%{$query}%")
                ->orWhere('en', 'ilike', "%{$query}%")
                ->orWhere('ar', 'ilike', "%{$query}%")
                ->orWhere('fr', 'ilike', "%{$query}%");
        })
            ->get()
            ->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'en' => $tag->en,
                    'ar' => $tag->ar,
                    'fr' => $tag->fr,
                ];
            })
            ->toArray();

        return response()->json(['tags' => $tags], 200);
    }



    public function submitNewTag(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tags,name',
            'category_id' => 'nullable|exists:categories,id',
            'en' => 'nullable|string|max:255',
            'ar' => 'nullable|string|max:255',
            'fr' => 'nullable|string|max:255',
        ]);

        $tag = Tag::create($validated);

        return response()->json([
            'tag' => [
                'id' => $tag->id,
                'name' => $tag->name,
                'en' => $tag->en,
                'ar' => $tag->ar,
                'fr' => $tag->fr,
            ],
        ], 201);
    }

    public function listPlatforms(Request $request)
    {
        return response()->json(OGMHelpers::GetPlatforms());
    }
}
