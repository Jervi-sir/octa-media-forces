<?php

namespace App\Http\Controllers\Api\v2_9\OGM;

use App\Helpers\v2_9\OGMHelpers;
use App\Http\Controllers\Controller;
use App\Models\Ogm;
use App\Models\OgmQualificationProgress;
use App\Models\Os;
use App\Models\PushNotificationToken;
use App\Models\Wilaya;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
  // username
  public function confirmUsernameAvailability(Request $request)
  {
    $request->validate([
      'username' => 'required',
    ]);
    $username = $request->input('username');
    $is_valid = true;
    $exists = Ogm::where('username', $username)->exists();
    if ($exists) $is_valid = false;
    return response()->json([
      'is_valid' => $is_valid
    ]);
  }

  public function register(Request $request)
  {
    $request->validate([
      'username' => 'required|string|unique:ogms,username|min:3|max:255',
      'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/',
      'phone_number' => 'required|string|unique:ogms,phone_number|regex:/^\+?[\d\s-]{10,}$/',
    ]);

    try {
      $ogm = Ogm::create([
        'displayed_id' => Str::random(16),
        'username' => $request->username,
        'password' => Hash::make($request->password),
        'password_plain_text' => $request->password,
        'phone_number' => $request->phone_number,
      ]);
      // Check and create OgmQualificationProgress
      // $this->ensureOgmQualificationProgress($ogm->id);

      $bearer_token = $ogm->createToken('ogm:api')->plainTextToken;

      return response()->json([
        'success' => true,
        'message' => 'Registered successfully',
        'bearer_token' => $bearer_token,
        'ogm' => OGMHelpers::OgmAccount($ogm),
      ], 201);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Registration failed: ' . $e->getMessage(),
      ], 500);
    }
  }



  public function login(Request $request)
  {
    $request->validate([
      'login' => 'required|string',
      'password' => 'required|string',
    ]);

    try {
      // Check if login is username or phone_number
      $ogm = Ogm::where('username', $request->login)
        ->orWhere('phone_number', $request->login)
        ->first();

      if (!$ogm || !Hash::check($request->password, $ogm->password)) {
        return response()->json([
          'success' => false,
          'message' => 'Invalid credentials',
        ], 401);
      }
      // Check and create OgmQualificationProgress
      // $this->ensureOgmQualificationProgress($ogm->id);

      $bearer_token = $ogm->createToken('ogm:api')->plainTextToken;

      return response()->json([
        'success' => true,
        'message' => 'Logged in successfully',
        'bearer_token' => $bearer_token,
        'ogm' => OGMHelpers::OgmAccount($ogm),
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Login failed: ' . $e->getMessage(),
      ], 500);
    }
  }



  public function logout(Request $request)
  {
    $user = $request->user();
    $deviceId = $request->header('X-Device-Id') ?? $request->input('device_id');
    if ($deviceId) {
      PushNotificationToken::query()
        ->where('owner_type', $user->getMorphClass())
        ->where('owner_id', $user->getKey())
        ->where('device_id', $deviceId)
        ->update([
          'expo_push_token' => null,
          'device_token'    => null,
          'is_active'       => false,
          'last_seen_at'    => now(),
          'updated_at'      => now(),
        ]);
    }
    // revoke current token last (so the updates above are authorized)
    $user->currentAccessToken()?->delete();

    return response()->json(['message' => 'Logged out']);
  }

  public function logoutAll(Request $request)
  {
    $user = $request->user();

    // deactivate/clear all push tokens for this user
    PushNotificationToken::query()
      ->where('owner_type', $user->getMorphClass())
      ->where('owner_id', $user->getKey())
      ->update([
        'expo_push_token' => null,
        'device_token'    => null,
        'is_active'       => false,
        'last_seen_at'    => now(),
        'updated_at'      => now(),
      ]);

    // revoke all access tokens
    $user->tokens()->delete();

    return response()->json(['message' => 'Logged out from all devices']);
  }

  public function validateAuthToken(Request $request) 
  {
      $ogm = $request->user();

      return response()->json([
        'me' => OGMHelpers::OgmAccount($ogm),
      ]);
  }

  public function quickGetMyStores(Request $request) 
  {
      $ogm = $request->user();
      $oses = Os::where('ogm_id', $ogm->id)->where('is_approved', true)->orderBy('store_name')->get();
      
      $data = [];
      foreach ($oses as $key => $os) {
        $data[$key] = [
          'id' => $os->id,
          'store_name' => $os->store_name,
          'image' => $os->image ? OGMHelpers::GenerateImageUrl($os->image) : null,
        ];
      }

      return response()->json([
        'stores' => $data,
      ]);
  }

  protected function ensureOgmQualificationProgress($ogmId)
  {
    $progress = OgmQualificationProgress::where('ogm_id', $ogmId)->first();

    if (!$progress) {
      OgmQualificationProgress::create([
        'ogm_id' => $ogmId,
        'status' => 'pending', // Set default status
        'progress' => [
          'intro_octaprise' => [
            'type' => 'start',
            'progress' => 0
          ]
        ],
      ]);
    }
  }
}
