<?php

namespace Tests\Feature;

use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ApiResponseTest extends TestCase
{
    public function test_success_response_uses_standard_shape(): void
    {
        $response = ApiResponse::success(['id' => 1], 'Created.', 201);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame([
            'success' => true,
            'data' => ['id' => 1],
            'message' => 'Created.',
        ], $response->getData(true));
    }

    public function test_validation_error_response_uses_standard_shape(): void
    {
        $response = ApiResponse::validationError(['amount' => ['The amount field is required.']]);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame([
            'success' => false,
            'message' => 'Validation failed.',
            'errors' => [
                'amount' => ['The amount field is required.'],
            ],
        ], $response->getData(true));
    }

    public function test_business_error_response_uses_standard_shape(): void
    {
        $response = ApiResponse::businessError('Insufficient balance.');

        $this->assertSame(409, $response->getStatusCode());
        $this->assertSame([
            'success' => false,
            'message' => 'Insufficient balance.',
        ], $response->getData(true));
    }

    public function test_unauthorized_response_uses_standard_shape(): void
    {
        $response = ApiResponse::unauthorized();

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame([
            'success' => false,
            'message' => 'Unauthenticated.',
        ], $response->getData(true));
    }

    public function test_api_validation_exception_is_rendered_with_standard_shape(): void
    {
        Route::post('/api/test-validation-response', function (Request $request) {
            $request->validate(['name' => ['required']]);

            return ApiResponse::success();
        });

        $this->postJson('/api/test-validation-response')
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'The name field is required.',
                'errors' => [
                    'name' => ['The name field is required.'],
                ],
            ]);
    }
}
