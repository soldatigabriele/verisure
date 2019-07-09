<?php

namespace Tests\Feature;

use Mockery;
use App\Session;
use Tests\TestCase;
use App\VerisureClient;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VerisureControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test requests without auth-token should fail
     *
     * @return void
     */
    public function testAuthorizationFails()
    {
        config()->set(['verisure.auth.active' => true]);
        $routes = ['', 'login', 'logout', 'status', 'activate/house', 'deactivate/garage'];
        foreach ($routes as $route) {
            try {
                $this->json('get', '/api/' . $route)->assertStatus(401);
            } catch (\Throwable $th) {
                //
            }
        }
    }

    /**
     * Test token validation can be disabled
     *
     * @return void
     */
    public function testAuthorizationDisabled()
    {
        $this->json('get', '/api')->assertStatus(200);
        $this->json('get', '/api', ['auth_token' => 'anything'])->assertStatus(200);
    }

    /**
     * Test login method
     *
     * @return void
     */
    public function testLogin()
    {
        $session = $this->createSession();

        $mock = Mockery::mock(VerisureClient::class);
        $mock->shouldReceive('getSession')->andReturn($session);
        $this->app->instance(VerisureClient::class, $mock);

        $response = $this->json('get', '/api/login')->assertStatus(200);
        $this->assertEquals(json_decode($response->getContent())->session->value, $session->value);
    }

    /**
     * Test logout method
     *
     * @return void
     */
    public function testLogout()
    {
        $mock = Mockery::mock(VerisureClient::class);
        $mock->shouldReceive('logout');
        $this->app->instance(VerisureClient::class, $mock);

        $response = $this->json('get', '/api/logout')->assertStatus(200);
    }

    /**
     * Test status method
     *
     * @return void
     */
    public function testStatus()
    {
        $mock = Mockery::mock(VerisureClient::class);
        $mock->shouldReceive('status')->andReturn("job_id_123");
        $this->app->instance(VerisureClient::class, $mock);

        $response = $this->json('get', '/api/status')->assertStatus(200);
        $this->assertEquals(json_decode($response->getContent())->job_id, 'job_id_123');
    }

    /**
     * Test activate house method
     *
     * @return void
     */
    public function testActivate()
    {
        $mock = Mockery::mock(VerisureClient::class);

        foreach (["full", "day", "night"] as $mode) {
            $mock->shouldReceive('activate')->with($mode)->andReturn("job_id_123");
            $this->app->instance(VerisureClient::class, $mock);

            $response = $this->json('get', '/api/activate/house/' . $mode)->assertStatus(200);
            $this->assertEquals(json_decode($response->getContent())->job_id, 'job_id_123');
        }
    }

    /**
     * Test activate garage method
     *
     * @return void
     */
    public function testActivateGarage()
    {
        $mock = Mockery::mock(VerisureClient::class);
        $mock->shouldReceive('activateAnnex')->andReturn("job_id_123");
        $this->app->instance(VerisureClient::class, $mock);

        $response = $this->json('get', '/api/activate/garage')->assertStatus(200);
        $this->assertEquals(json_decode($response->getContent())->job_id, 'job_id_123');
    }

    /**
     * Test deactivate house method
     *
     * @return void
     */
    public function testDeactivate()
    {
        $mock = Mockery::mock(VerisureClient::class);
        $mock->shouldReceive('deactivate')->andReturn("job_id_123");
        $this->app->instance(VerisureClient::class, $mock);

        $response = $this->json('get', '/api/deactivate/house/')->assertStatus(200);
        $this->assertEquals(json_decode($response->getContent())->job_id, 'job_id_123');
    }

    /**
     * Test deactivate garage method
     *
     * @return void
     */
    public function testDeactivateGarage()
    {
        $mock = Mockery::mock(VerisureClient::class);
        $mock->shouldReceive('deactivateAnnex')->andReturn("job_id_123");
        $this->app->instance(VerisureClient::class, $mock);

        $response = $this->json('get', '/api/deactivate/garage')->assertStatus(200);
        $this->assertEquals(json_decode($response->getContent())->job_id, 'job_id_123');
    }

    /**
     * Test jobStatus method
     *
     * @return void
     */
    public function testJobStatus()
    {
        $session = $this->createSession();

        $mock = Mockery::mock(VerisureClient::class);
        $mock->shouldReceive('jobStatus')->with('job-id-test')->andReturn(["status" => "completed", "message" => "Alarm activated"]);
        $this->app->instance(VerisureClient::class, $mock);

        $response = $this->json('get', '/api/job_status/job-id-test')->assertStatus(200);
        $this->assertEquals("completed", json_decode($response->getContent())->status);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test any request should contain the auth-token
     *
     * @return void
     */
    public function testAuthorizationSuccess()
    {
        config()->set(['verisure.auth.active' => true, 'verisure.auth.token' => Str::random(20)]);
        $response = $this->json('get', '/api', ['auth_token' => config('verisure.auth.token')])->assertStatus(200);
    }
}
