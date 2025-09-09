<?php

namespace App\Http\Controllers\Api\v2_9\OGM;

use App\Helpers\v2_9\OGMHelpers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class M5MeController extends Controller
{
    public function profile(Request $request) {
        $auth = $request->user();
        
        $me = OGMHelpers::OgmAccount($auth);
        $stores = $auth->stores()->where('is_approved', true)->count();
        $pending_stores = $auth->stores()->where('is_approved', false)->count();
        // $data['stores'] = $stores->map(fn($store) => OGMHelpers::OsFormat($store));

        return response()->json([
            'me' => $me,
            'nb_stores' => $stores,
            'nb_pending_stores' => $pending_stores
        ]);
    }

    public function getSettings(Request $request)
    {
        $user = $request->user();
        $validator = Validator::make($request->query(), [
            'fields' => 'required|string|in:username,phone_number,password,all',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }
        $fields = $request->query('fields', 'all'); // Default to 'all' if not specified
        $data = [];
        if ($fields === 'username') {
            $data = [
                'username' => $user->username,
            ];
        }
        if ($fields === 'phone_number') {
            $data = [
                'phone_number' => $user->phone_number,
            ];
        }
        if ($fields === 'password') {
            $data = [
                'password' => $user->password_plain_text,
            ];
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $data,
        ], 200);
    }

    // The updateSettings method remains unchanged
    public function updateSettings(Request $request)
    {
        $user = $request->user();
        $validator = Validator::make($request->all(), [
            'current_password' => [
                'required_with:new_password',
                function ($attribute, $value, $fail) use ($user) {
                    if (!Hash::check($value, $user->password)) {
                        $fail('The current password is incorrect.');
                    }
                },
            ],
            'new_password' => [
                'nullable',
                'string',
                'min:8',
                // 'confirmed',
            ],
            'username' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('ogms')->ignore($user->id),
            ],
            'phone_number' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('ogms')->ignore($user->id),
            ],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }
        $updateData = [];
        if ($request->filled('new_password')) {
            $updateData['password'] = Hash::make($request->new_password);
            $updateData['password_plain_text'] = $request->new_password;
        }
        if ($request->filled('username')) {
            $updateData['username'] = $request->username;
        }
        if ($request->filled('phone_number')) {
            $updateData['phone_number'] = $request->phone_number;
        }
        if (!empty($updateData)) {
            $user->update($updateData);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Settings updated successfully.',
            'data' => [
                'username' => $user->username,
                'phone_number' => $user->phone_number,
            ],
        ], 200);
    }
}













