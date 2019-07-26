<?php

namespace Tests\Unit\Http\Controllers;

use Tests\TestCase;
use App\VerisureClient;
use Illuminate\Support\Str;

class AuthenticateTokenTest extends TestCase
{
    /**
     * SetUp
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        config()->set(['verisure.auth.active' => true, 'verisure.auth.token' => Str::random(32)]);
    }

    /**
     * Test requests without auth-token should fail
     *
     * @return void
     */
    public function testAuthorizationFails()
    {
        $routes = ['', 'login', 'logout', 'status', 'activate/house', 'deactivate/garage'];
        foreach ($routes as $route) {
            $this->json('get', '/api/' . $route)->assertStatus(401);
        }
    }

    /**
     * Test requests with invalid auth-token should fail
     *
     * @return void
     */
    public function testAuthorizationFailsWrongToken()
    {
        $routes = ['', 'login', 'logout', 'status', 'activate/house', 'deactivate/garage'];
        foreach ($routes as $route) {
            $this->json('get', '/api/' . $route, ['auth_token' => 'invalid_token'])->assertStatus(401);
        }
    }

    /**
     * Test token validation can be disabled
     *
     * @return void
     */
    public function testAuthorizationDisabled()
    {
        config()->set(['verisure.auth.active' => false]);
        $this->json('get', '/api')->assertStatus(200);
        $this->json('get', '/api', ['auth_token' => 'anything'])->assertStatus(200);
    }

    /**
     * Test any request should contain the auth-token
     *
     * @return void
     */
    public function testAuthorizationSuccess()
    {
        $this->json('get', '/api', ['auth_token' => config('verisure.auth.token')])->assertStatus(200);
    }
}
