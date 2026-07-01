<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

final class ApiResponse
{
    public static function success(mixed $data = null, ?string $message = null, int $status = 200): JsonResponse
    {
        $payload = [
            'success' => true,
            'data' => $data,
        ];

        if ($message !== null) {
            $payload['message'] = $message;
        }

        return response()->json($payload, $status);
    }

    public static function error(string $message, int $status, array $errors = []): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== []) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    public static function validationError(array $errors, string $message = 'Validation failed.'): JsonResponse
    {
        return self::error($message, 422, $errors);
    }

    public static function businessError(string $message, int $status = 409): JsonResponse
    {
        return self::error($message, $status);
    }

    public static function unauthorized(string $message = 'Unauthenticated.'): JsonResponse
    {
        return self::error($message, 401);
    }
}
