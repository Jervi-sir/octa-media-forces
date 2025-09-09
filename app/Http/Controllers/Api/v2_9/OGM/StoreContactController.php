<?php

namespace App\Http\Controllers\Api\v2_9\OGM;

use App\Http\Controllers\Controller;
use App\Models\ContactPlatform;
use App\Models\Os;
use App\Models\OsContactList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StoreContactController extends Controller
{
    public function listContacts($os_id, Request $request) {}
    public function addContact(Request $request, $osId)
    {
        try {
            $store = Os::find($osId);
            if (!$store) {
                return response()->json(['error' => 'Store not found'], 404);
            }
            $validator = Validator::make($request->all(), [
                'contact_platforms_id' => 'required|exists:contact_platforms,id',
                'link' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $contact = OsContactList::create([
                'os_id' => $osId,
                'contact_platforms_id' => $request->contact_platforms_id,
                'link' => $request->link,
            ]);
            $platform = ContactPlatform::find($contact->contact_platforms_id);
            return response()->json([
                'id' => $contact->id,
                'os_id' => $osId,
                'link' => $contact->link,
                'contact_platforms_id' => $platform->id,
                'platform' => [
                    'id' => $platform->id,
                    'name' => $platform->name,
                    'code_name' => $platform->code_name,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to add contact'], 500);
        }
    }

    public function updateContact(Request $request, $osId, $contactId)
    {
        try {
            $store = Os::find($osId);
            if (!$store) {
                return response()->json(['error' => 'Store not found'], 404);
            }
            $contact = OsContactList::where('id', $contactId)->where('os_id', $osId)->first();
            if (!$contact) {
                return response()->json(['error' => 'Contact not found'], 404);
            }
            $validator = Validator::make($request->all(), [
                'contact_platforms_id' => 'required|exists:contact_platforms,id',
                'link' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $contact->update([
                'contact_platforms_id' => $request->contact_platforms_id,
                'link' => $request->link,
            ]);
            $platform = ContactPlatform::find($contact->contact_platforms_id);
            return response()->json([
                'id' => $contact->id,
                'link' => $contact->link,
                'os_id' => $osId,
                'contact_platforms_id' => $platform->id,
                'platform' => [
                    'id' => $platform->id,
                    'name' => $platform->name,
                    'code_name' => $platform->code_name,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update contact'], 500);
        }
    }

    public function deleteContact($osId, $contactId)
    {
        try {
            $store = Os::find($osId);
            if (!$store) {
                return response()->json(['error' => 'Store not found'], 404);
            }
            $contact = OsContactList::where('id', $contactId)->where('os_id', $osId)->first();
            if (!$contact) {
                return response()->json(['error' => 'Contact not found'], 404);
            }
            $contact->delete();
            return response()->json(['message' => 'Contact deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete contact'], 500);
        }
    }

}
