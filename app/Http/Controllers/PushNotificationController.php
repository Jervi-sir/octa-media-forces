<?php

namespace App\Http\Controllers;

use App\Models\Ogm;
use App\Models\PushNotificationToken;
use App\Services\ExpoPushService;
use Illuminate\Http\Request;

class PushNotificationController extends Controller
{
    public function sendToCurrentUser(Request $request, ExpoPushService $expo)
    {
        $user = Ogm::first();

        $tokens = PushNotificationToken::query()
            ->where('owner_type', $user->getMorphClass())
            ->where('owner_id', $user->getKey())
            ->where('is_active', true)
            ->pluck('expo_push_token')
            ->filter()
            ->values()
            ->all();

        if (empty($tokens)) {
            return response()->json(['message' => 'No active push tokens found'], 404);
        }

        $result = $expo->send($tokens, 'Hello from Laravel', 'This is a test notification', [
            'customData' => '1234',
        ]);

        return response()->json(['result' => $result]);
    }

}
