<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BasicApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function api_returns_unauthorized_for_protected_routes()
    {
        $response = $this->getJson('/api/v1/dashboard');

        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.'
                ]);
    }

    /** @test */
    public function api_returns_unauthorized_for_produtores_route()
    {
        $response = $this->getJson('/api/v1/produtores-rurais');

        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.'
                ]);
    }

    /** @test */
    public function api_returns_unauthorized_for_propriedades_route()
    {
        $response = $this->getJson('/api/v1/propriedades');

        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.'
                ]);
    }

    /** @test */
    public function api_returns_unauthorized_for_rebanhos_route()
    {
        $response = $this->getJson('/api/v1/rebanhos');

        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.'
                ]);
    }

    /** @test */
    public function api_returns_unauthorized_for_unidades_route()
    {
        $response = $this->getJson('/api/v1/unidades-producao');

        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.'
                ]);
    }

    /** @test */
    public function api_returns_unauthorized_for_relatorios_route()
    {
        $response = $this->getJson('/api/v1/relatorios/produtores-rurais');

        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.'
                ]);
    }

    /** @test */
    public function authenticated_user_can_access_dashboard()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
                         ->getJson('/api/v1/dashboard');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'estatisticas'
                    ]
                ]);
    }

    /** @test */
    public function authenticated_user_can_access_produtores()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
                         ->getJson('/api/v1/produtores-rurais');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data',
                        'current_page',
                        'last_page',
                        'per_page',
                        'total'
                    ]
                ]);
    }

    /** @test */
    public function api_returns_json_content_type()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
                         ->getJson('/api/v1/dashboard');

        $response->assertHeader('content-type', 'application/json');
    }

    /** @test */
    public function api_handles_invalid_route()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
                         ->getJson('/api/v1/invalid-route');

        $response->assertStatus(404);
    }

    /** @test */
    public function api_returns_success_true_for_valid_routes()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
                         ->getJson('/api/v1/dashboard');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true
                ]);
    }
}
