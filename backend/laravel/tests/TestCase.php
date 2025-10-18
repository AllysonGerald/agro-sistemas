<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Creates the application.
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Criar usuário de teste (sem autenticação automática)
        $this->user = User::factory()->create([
            'email' => 'test@agrosistemas.com',
            'password' => bcrypt('password')
        ]);
    }

    /**
     * Fazer requisição autenticada
     */
    protected function authenticatedJson($method, $uri, array $data = [], array $headers = [])
    {
        return $this->json($method, $uri, $data, array_merge([
            'Authorization' => 'Bearer ' . $this->user->createToken('test')->plainTextToken
        ], $headers));
    }

    /**
     * Fazer requisição GET autenticada
     */
    protected function authenticatedGet($uri, array $headers = [])
    {
        return $this->authenticatedJson('GET', $uri, [], $headers);
    }

    /**
     * Fazer requisição POST autenticada
     */
    protected function authenticatedPost($uri, array $data = [], array $headers = [])
    {
        return $this->authenticatedJson('POST', $uri, $data, $headers);
    }

    /**
     * Fazer requisição PUT autenticada
     */
    protected function authenticatedPut($uri, array $data = [], array $headers = [])
    {
        return $this->authenticatedJson('PUT', $uri, $data, $headers);
    }

    /**
     * Fazer requisição DELETE autenticada
     */
    protected function authenticatedDelete($uri, array $headers = [])
    {
        return $this->authenticatedJson('DELETE', $uri, [], $headers);
    }
}
