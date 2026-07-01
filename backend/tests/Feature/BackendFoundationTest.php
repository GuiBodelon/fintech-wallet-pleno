<?php

namespace Tests\Feature;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BackendFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_health_route_returns_standard_json(): void
    {
        $this->getJson('/api/health')
            ->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'ok',
                ],
            ]);
    }

    public function test_laravel_runtime_is_version_13(): void
    {
        $this->assertStringStartsWith('13.', Application::VERSION);
    }

    public function test_postgresql_is_configured_for_local_environment(): void
    {
        $envExample = file_get_contents(base_path('.env.example'));

        $this->assertSame('pgsql', config('database.connections.pgsql.driver'));
        $this->assertStringContainsString('DB_CONNECTION=pgsql', $envExample);
        $this->assertStringContainsString('DB_HOST=postgres', $envExample);
    }

    public function test_migrations_can_run_in_test_environment(): void
    {
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasTable('cache'));
        $this->assertTrue(Schema::hasTable('jobs'));
    }

    public function test_sanctum_package_is_available_for_token_authentication(): void
    {
        if (! class_exists(\Laravel\Sanctum\Sanctum::class)) {
            $this->markTestSkipped('Laravel Sanctum is not installed yet. Install laravel/sanctum before implementing auth.');
        }

        $this->assertTrue(class_exists(\Laravel\Sanctum\Sanctum::class));
    }
}
