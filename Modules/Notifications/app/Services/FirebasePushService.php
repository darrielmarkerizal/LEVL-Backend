<?php

declare(strict_types=1);

namespace Modules\Notifications\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebasePushService
{
    private ?array $credentials = null;

    public function sendToToken(string $token, string $title, string $message, ?array $data = null): bool
    {
        $credentials = $this->getCredentials();
        if ($credentials === null) {
            return false;
        }

        $accessToken = $this->fetchAccessToken($credentials);
        if ($accessToken === null) {
            return false;
        }

        $projectId = (string) ($credentials['project_id'] ?? config('services.firebase.project_id'));
        if ($projectId === '') {
            Log::warning('FCM project id is not configured');
            return false;
        }

        $payload = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $message,
                ],
            ],
        ];

        if (! empty($data)) {
            $payload['message']['data'] = collect($data)
                ->mapWithKeys(fn ($value, $key) => [(string) $key => is_scalar($value) ? (string) $value : json_encode($value)])
                ->toArray();
        }

        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", $payload);

        if (! $response->successful()) {
            Log::warning('Failed to send FCM message', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return false;
        }

        return true;
    }

    private function getCredentials(): ?array
    {
        if ($this->credentials !== null) {
            return $this->credentials;
        }

        $path = (string) config('services.firebase.credentials_path');
        if ($path === '' || ! is_file($path)) {
            Log::warning('FCM credentials file not found', ['path' => $path]);
            return null;
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            Log::warning('Unable to read FCM credentials file', ['path' => $path]);
            return null;
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            Log::warning('FCM credentials file is invalid JSON', ['path' => $path]);
            return null;
        }

        $this->credentials = $decoded;

        return $this->credentials;
    }

    private function fetchAccessToken(array $credentials): ?string
    {
        $clientEmail = (string) ($credentials['client_email'] ?? '');
        $privateKey = (string) ($credentials['private_key'] ?? '');
        $tokenUri = (string) ($credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token');

        if ($clientEmail === '' || $privateKey === '') {
            Log::warning('FCM credentials missing required keys');
            return null;
        }

        $now = time();
        $header = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $claims = $this->base64UrlEncode(json_encode([
            'iss' => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => $tokenUri,
            'iat' => $now,
            'exp' => $now + 3600,
        ]));

        $unsignedJwt = $header.'.'.$claims;
        $signature = '';
        $signed = openssl_sign($unsignedJwt, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        if (! $signed) {
            Log::warning('Unable to sign FCM JWT assertion');
            return null;
        }

        $jwt = $unsignedJwt.'.'.$this->base64UrlEncode($signature);
        $response = Http::asForm()->acceptJson()->post($tokenUri, [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        if (! $response->successful()) {
            Log::warning('Unable to fetch FCM OAuth access token', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        return (string) $response->json('access_token');
    }

    private function base64UrlEncode(string $input): string
    {
        return rtrim(strtr(base64_encode($input), '+/', '-_'), '=');
    }
}
