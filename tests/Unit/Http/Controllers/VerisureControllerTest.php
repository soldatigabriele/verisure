<?php

namespace Tests\Unit\Http\Controllers;

use App\Jobs\Login;
use Tests\TestCase;
use App\Jobs\Status;
use App\Jobs\Activate;
use App\VerisureClient;
use App\Jobs\ActivateAnnex;
use App\Jobs\ActivateHouse;
use App\Jobs\RequestStatus;
use Illuminate\Support\Str;
use App\Jobs\DeactivateAnnex;
use App\Jobs\DeactivateHouse;
use Illuminate\Support\Facades\Queue;

class VerisureControllerTest extends TestCase
{
    /**
     * SetUp
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        Queue::fake();
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
        $this->mock(VerisureClient::class, function ($mock) {
            $mock->shouldReceive('logout')->once();
        });
        $this->json('get', '/api/logout')->assertStatus(200);
    }

    /**
     * Test status method
     *
     * @return void
     */
    public function testStatus()
    {
        $this->json('get', '/api/status')->assertStatus(202);
        Queue::assertPushed(RequestStatus::class);
    }

    /**
     * Test activate house method dispatches the correct job
     *
     * @return void
     */
    public function testActivateHouse()
    {
        foreach (["house", "day", "night"] as $mode) {
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
}
