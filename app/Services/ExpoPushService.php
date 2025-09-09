<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ExpoPushService
{
    protected Client $client;
    protected string $endpoint = 'https://exp.host/--/api/v2/push/send';

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => $this->endpoint,
            'timeout' => 10,
        ]);
    }

    /**
     * Send a push notification to one or more Expo tokens
     *
     * @param array|string $tokens
     * @param string $title
     * @param string $body
     * @param array $data
     * @return array
     */
    public function send($tokens, string $title, string $body, array $data = []): array
    {
        if (is_string($tokens)) {
            $tokens = [$tokens];
        }

        $messages = [];
        foreach ($tokens as $token) {
            // Only send to valid Expo push tokens
            if (!preg_match('/^ExponentPushToken\[.+\]$/', $token)) {
                Log::warning("Invalid Expo push token: {$token}");
                continue;
            }

            $messages[] = [
                'to' => $token,
                'sound' => 'default',
                'title' => $title,
                'body' => $body,
                'data' => $data,
            ];
        }

        if (empty($messages)) {
            return ['error' => 'No valid Expo tokens'];
        }

        try {
            $response = $this->client->post('', [
                'headers' => ['Accept' => 'application/json', 'Content-Type' => 'application/json'],
                'json' => $messages,
            ]);

            return json_decode((string) $response->getBody(), true);
        } catch (\Exception $e) {
            Log::error('Expo push send failed: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
}
