<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * @param  array<string, mixed>  $data
     */
    public static function success(array $data = [], string $msg = 'ok', int $httpStatus = 200): JsonResponse
    {
        return response()->json([
            'code' => 0,
            'msg' => $msg,
            'data' => $data,
        ], $httpStatus);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function error(string $msg, int $code = 1, int $httpStatus = 400, array $data = []): JsonResponse
    {
        return response()->json([
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ], $httpStatus);
    }
}
