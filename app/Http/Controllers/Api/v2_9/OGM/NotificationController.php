<?php

namespace App\Http\Controllers\Api\v2_9\OGM;

use App\Http\Controllers\Controller;
use App\Models\OgmNotification;
use App\Models\PushNotificationToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function subscribeToPushNotifications(Request $request)
    {
        $user = $request->user(); // bearer-authenticated user

        $data = $request->validate([
            'platform'         => 'required|string|in:ios,android',
            'expo_push_token'  => 'required|string',
            'device_token'     => 'nullable|string',
            'device_id'        => 'required|string|max:64',
            'device_model'     => 'nullable|string',
            'os_version'       => 'nullable|string',
            'app_version'      => 'nullable|string',
            'locale'           => 'nullable|string|max:16',
            'is_active'        => 'nullable|boolean',
        ]);

        // Enforce owner from auth (ignore any incoming owner_*)
        $ownerType = $user->getMorphClass();
        $ownerId   = $user->getKey();

        // Respect your unique(['owner_type','owner_id','device_id'])
        $existing = PushNotificationToken::query()
            ->where('owner_type', $ownerType)
            ->where('owner_id', $ownerId)
            ->where('device_id', $data['device_id'])
            ->first();

        if ($existing) {
            // already exists → client expects 409
            return response()->json([
                'message' => 'Token for this device already exists.',
                'id'      => $existing->id,
            ], 409);
        }

        $token = new PushNotificationToken();
        $token->fill($data);
        $token->owner_type   = $ownerType;
        $token->owner_id     = $ownerId;
        $token->is_active    = array_key_exists('is_active', $data) ? (bool)$data['is_active'] : true;
        $token->last_seen_at = now();
        $token->save();

        return response()->json($token, 201);
    }

    public function updatePushNotifications(Request $request, string $deviceId)
    {
        $user = $request->user();

        $data = $request->validate([
            'expo_push_token'  => 'nullable|string',
            'device_token'     => 'nullable|string',
            'device_model'     => 'nullable|string',
            'os_version'       => 'nullable|string',
            'app_version'      => 'nullable|string',
            'locale'           => 'nullable|string|max:16',
            'is_active'        => 'nullable|boolean',
        ]);

        $token = PushNotificationToken::query()
            ->where('owner_type', $user->getMorphClass())
            ->where('owner_id', $user->getKey())
            ->where('device_id', $deviceId)
            ->first();

        if (!$token) {
            return response()->json(['message' => 'Device token not found.'], 404);
        }

        $token->fill($data);
        $token->last_seen_at = now();
        $token->save();

        return response()->json($token);
    }


    public function listNotifications(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        $ogm = $request->user();
        $notifications = OgmNotification::with(['ogmNotificationType'])
            ->where('ogm_id', $ogm->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
        // Transform the response to include relevant data
        $response = $notifications->through(function ($notification) {
            return [
                'id' => $notification->id,
                'ogm_id' => $notification->ogm_id,
                'notification_type' => $notification->ogmNotificationType ? [
                    'id' => $notification->ogmNotificationType->id,
                    'name' => $notification->ogmNotificationType->name,
                    'title_en' => $notification->ogmNotificationType->title_en,
                    'title_ar' => $notification->ogmNotificationType->title_ar,
                    'title_fr' => $notification->ogmNotificationType->title_fr,

                    'content_en' => $notification->ogmNotificationType->content_en,
                    'content_ar' => $notification->ogmNotificationType->content_ar,
                    'content_fr' => $notification->ogmNotificationType->content_fr,

                    'icon' => $notification->ogmNotificationType->icon,
                ] : null,
                'content' => $notification->content,
                'is_opened' => $notification->is_opened,
                'created_at' => $notification->created_at,
                'updated_at' => $notification->updated_at,
            ];
        });

        return response()->json([
            'data' => $response->items(),
            'next_page' => $response->hasMorePages() ? $response->currentPage() + 1 : null,

        ]);
    }

    private function getActionButton($notification)
    {
        // Add logic to determine if action button is needed
        // Example for payment failure
        if (str_contains(strtolower($notification->type->name), 'payment')) {
            return ['title' => 'Retry payment operation'];
        }
        return null;
    }

    public function markAsOpened($id)
    {
        $notification = OgmNotification::where('ogm_id', Auth::id())
            ->findOrFail($id);

        $notification->update(['is_opened' => true]);

        return response()->json(['message' => 'Notification marked as opened']);
    }
}
