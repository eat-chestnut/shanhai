<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiException;
use App\Models\PlayerProfile;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticatePlayer
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = trim(str_replace('Bearer', '', (string) $request->header('Authorization')));

        if ($token === '') {
            $token = trim((string) $request->header('X-Player-Token', ''));
        }

        if ($token === '') {
            throw new ApiException('未登录', 40101, 401);
        }

        $playerProfile = PlayerProfile::query()
            ->where('auth_token', $token)
            ->first();

        if (! $playerProfile) {
            throw new ApiException('未登录', 40101, 401);
        }

        $request->attributes->set('playerProfile', $playerProfile);

        return $next($request);
    }
}
