<?php

namespace Tests\Unit\Http\Controllers;

use Mockery;
use App\Jobs\Login;
use Tests\TestCase;
use App\Jobs\Status;
use App\Jobs\Activate;
use App\VerisureClient;
use App\Jobs\ActivateAnnex;
use App\Jobs\ActivateHouse;
use Illuminate\Support\Str;
use App\Events\RecordCreated;
use App\Events\StatusCreated;
use App\Jobs\DeactivateAnnex;
use App\Jobs\DeactivateHouse;
use App\Status as LocalStatus;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VerisureControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    /**
     * Test requests without auth-token should fail
     *
     * @return void
     */
    public function testAuthorizationFails()
    {
        $this->app->instance(VerisureClient::class, Mockery::mock(VerisureClient::class));
        config()->set(['verisure.auth.active' => true]);
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
        $this->app->instance(VerisureClient::class, Mockery::mock(VerisureClient::class));
        config()->set(['verisure.auth.active' => true]);
        config()->set(['verisure.auth.token' => Str::random(32)]);
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
        $this->json('get', '/api/login')->assertStatus(202);
        Queue::assertPushed(Login::class);
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
        $mock->shouldReceive('status')->andReturn($jobId = Str::random(20));
        $this->app->instance(VerisureClient::class, $mock);

        $response = $this->json('get', '/api/status')->assertStatus(200);
        $this->assertEquals(json_decode($response->getContent())->job_id, $jobId);
        Queue::assertPushedOn('high', Status::class);
        Queue::assertPushed(Status::class, function ($job) use ($jobId) {
            return $job->jobId === $jobId;
        });
    }

    /**
     * Test activate house method dispatches the correct job
     *
     * @return void
     */
    public function testActivateHouse()
    {
        foreach (["full", "day", "night"] as $mode) {
            $this->json('get', '/api/activate/house/' . $mode)->assertStatus(202);
            Queue::assertPushed(ActivateHouse::class, function ($job) use ($mode) {
                return $job->mode === $mode;
            });
        }
    }

    /**
     * Test activate garage method dispatches the correct job
     *
     * @return void
     */
    public function testActivateGarage()
    {
        $this->json('get', '/api/activate/garage')->assertStatus(202);
        Queue::assertPushed(ActivateAnnex::class);
    }

    /**
     * Test activate wrong system
     *
     * @return void
     */
    public function testActivateWrongSystem()
    {
        $this->json('get', '/api/activate/' . Str::random(50))->assertStatus(400);
        Queue::assertNotPushed(ActivateAnnex::class);
        Queue::assertNotPushed(ActivateHouse::class);
    }

    /**
     * Test deactivate wrong system
     *
     * @return void
     */
    public function testDeactivateWrongSystem()
    {
        $this->json('get', '/api/deactivate/' . Str::random(50))->assertStatus(400);
        Queue::assertNotPushed(DeactivateAnnex::class);
        Queue::assertNotPushed(DeactivateHouse::class);
    }

    /**
     * Test deactivate house method
     *
     * @return void
     */
    public function testDeactivateHouse()
    {
        $this->json('get', '/api/deactivate/house')->assertStatus(202);
        Queue::assertPushed(DeactivateHouse::class);
    }

    /**
     * Test deactivate garage method
     *
     * @return void
     */
    public function testDeactivateGarage()
    {
        $this->json('get', '/api/deactivate/garage')->assertStatus(202);
        Queue::assertPushed(DeactivateAnnex::class);
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
