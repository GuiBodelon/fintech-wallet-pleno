<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Gui Bodelon',
            'email' => 'gui@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', 'gui@example.com')
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'gui@example.com',
        ]);
    }

    public function test_registration_creates_wallet_with_zero_balance(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Gui Bodelon',
            'email' => 'gui@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertCreated();

        $user = User::query()->where('email', 'gui@example.com')->firstOrFail();

        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'balance_cents' => 0,
        ]);
    }

    public function test_user_can_login(): void
    {
        User::factory()->create([
            'email' => 'gui@example.com',
            'password' => 'password',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'gui@example.com',
            'password' => 'password',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', 'gui@example.com')
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                ],
            ]);
    }

    public function test_protected_route_rejects_unauthenticated_request(): void
    {
        $this->getJson('/api/me')
            ->assertUnauthorized()
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $this->postJson('/api/logout', [], [
            'Authorization' => "Bearer {$token}",
        ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame(0, PersonalAccessToken::query()->count());
    }
}
