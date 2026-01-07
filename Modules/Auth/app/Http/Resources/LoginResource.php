<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;



class LoginResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user' => new UserResource($this['user']),
            'access_token' => $this['access_token'],
            'refresh_token' => $this['refresh_token'],
            'expires_in' => $this['expires_in'],
            'message' => $this['message'] ?? null,
        ];
    }
}
