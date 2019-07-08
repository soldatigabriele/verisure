<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VerisureControllerTest extends TestCase
{
    /**
     * Test requests without auth-token should fail
     *
     * @return void
     */
    public function testAuthorizationFails()
    {
        $routes = ['', 'login', 'logout', 'status', 'activate/1', 'deactivate/1'];
        foreach($routes as $route){
            $this->json('get', '/api/'. $route)->assertStatus(401);
        }
    }

    /**
     * Test token validation can be disabled
     *
     * @return void
     */
    public function testAuthorizationDisabled()
    {
        config()->set(['verisure.auth-token' => false]);
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
        config()->set(['verisure.auth-token' => Str::random(20)]);
        $response = $this->json('get', '/api', ['auth_token' => config('verisure.auth-token')])->assertStatus(200);
    }
}
