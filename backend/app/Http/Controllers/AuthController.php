<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = DB::transaction(function () use ($request): array {
            $user = User::query()->create($request->safe()->only([
                'name',
                'email',
                'password',
            ]));

            $user->wallet()->create([
                'balance_cents' => 0,
            ]);

            return $this->tokenResponseData($user);
        });

        return ApiResponse::success($data, 'Registration completed successfully.', 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::query()
            ->where('email', $request->validated('email'))
            ->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            return ApiResponse::unauthorized('Invalid credentials.');
        }

        return ApiResponse::success($this->tokenResponseData($user), 'Login completed successfully.');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return ApiResponse::success(null, 'Logout completed successfully.');
    }

    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success([
            'user' => $this->userPayload($request->user()),
        ]);
    }

    /**
     * @return array{user: array<string, mixed>, token: string}
     */
    private function tokenResponseData(User $user): array
    {
        return [
            'user' => $this->userPayload($user),
            'token' => $user->createToken('api-token')->plainTextToken,
        ];
    }

    /**
     * @return array{id: int, name: string, email: string}
     */
    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];
    }
}
