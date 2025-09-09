<?php

namespace App\Http\Controllers\Api\v2_9\OGM;

use App\Helpers\v2_9\OGMHelpers;
use App\Http\Controllers\Controller;
use App\Models\Os;
use App\Models\OsContactList;
use App\Models\Wilaya;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StoreController extends Controller
{
    public function listStores(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'is_approved' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors(),
            ], 422);
        }
        $isApproved = filter_var($request->is_approved, FILTER_VALIDATE_BOOLEAN);

        try {
            // Fetch stores for the authenticated user
            $stores = Os::where('ogm_id', Auth::id())
                ->where('is_approved', $isApproved === true)
                ->orderBy('id', 'desc')
                ->with(['wilaya'])
                ->get();
            $storesData = $stores->map(fn($store) => OGMHelpers::OsFormat($store));

            return response()->json([
                'stores' => $storesData,
                'nb_stores' => $stores->count(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch stores',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function createNewStores(Request $request)
    {
        $data = $request->validate([
            'is_approved'   => ['sometimes','boolean'],
            'store_name'    => ['required','string','max:255'],
            'wilaya_id'     => ['required','integer','exists:wilayas,id'],
            'image'         => ['nullable','string','max:1024'],
            'phone_number'  => ['nullable','string','max:64'],
            'map_link'      => ['nullable','string','max:1024'],
            'bio'           => ['nullable','string'],
            'contacts'      => ['array'],
            'contacts.*.contact_platforms_id' => ['required','integer','exists:contact_platforms,id'],
            'contacts.*.link' => ['required','string','max:1024'],
        ]);

        return DB::transaction(function () use ($data, $request) {
            $store = Os::create([
                'ogm_id'       => $request->user()->id,  // or however you attach it
                'wilaya_id'    => $data['wilaya_id'],
                'store_name'   => $data['store_name'],
                'image'        => $data['image'] ?? null,
                'phone_number' => $data['phone_number'] ?? null,
                'map_link'     => $data['map_link'] ?? null,
                'bio'          => $data['bio'] ?? null,
            ]);

            $contacts = collect($data['contacts'] ?? [])
                ->map(fn($c) => [
                    'os_id'                 => $store->id,
                    'contact_platforms_id'  => $c['contact_platforms_id'],
                    'link'                  => $c['link'],
                    'created_at'            => now(),
                    'updated_at'            => now(),
                ])->all();

            if (!empty($contacts)) {
                OsContactList::insert($contacts);
            }

            // $store->load(['wilaya', 'contacts.platform']); // eager for response
            return response()->json(['store' => OGMHelpers::OsFormat($store)], 201);
        });
    }

    public function showStore($os_id, Request $request)
    {
        $os = Os::find($os_id);
        $wilayas = Wilaya::all();
        $contacts = OsContactList::with('contactPlatform')->where('os_id', $os_id)->get();
        $contactLists = OsContactList::with('contactPlatform')
            ->where('os_id', $os_id)
            ->get()
            ->map(function ($contact) {
                return OsContactList::ContactFormat($contact);
            });
        return response()->json([
            'store' => OGMHelpers::OsFormat($os),
            'contacts' => $contactLists,
            'platforms' => OGMHelpers::GetPlatforms(),
            'wilayas' => $wilayas->map(fn($wilaya) => OGMHelpers::WilayaFormat($wilaya)),
        ]);
    }

    public function updateStore($os_id, Request $request)
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'image' => 'nullable|string',
            'store_name' => 'required|string|max:255',
            'wilaya_id' => 'nullable|integer|exists:wilayas,id', // Assuming a wilayas table
            'phone_number' => 'nullable|string|max:20',
            'map_link' => 'nullable|url|max:255',
            'bio' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors(),
            ], 422);
        }

        try {
            // Find the store by ID
            $store = Os::findOrFail($os_id);

            // Update store with validated data
            $store->update([
                'image' => $request->input('image'),
                'store_name' => $request->input('store_name'),
                'wilaya_id' => $request->input('wilaya_id'),
                'phone_number' => $request->input('phone_number'),
                'map_link' => $request->input('map_link'),
                'bio' => $request->input('bio'),
            ]);

            $updated_store = $store->fresh();

            // Return the updated store data
            return response()->json([
                'message' => 'Store updated successfully',
                'store' => OGMHelpers::OsFormat($updated_store), // Reload the model to include any updated relationships
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Store not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update store',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
