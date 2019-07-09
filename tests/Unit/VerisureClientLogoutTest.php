<?php

namespace Tests\Unit;

use Mockery;
use App\Session;
use Carbon\Carbon;
use Tests\TestCase;
use App\VerisureClient;
use Illuminate\Support\Str;
use GuzzleHttp\Psr7\Response;
use App\Exceptions\LogoutException;
use App\Exceptions\StatusException;
use App\Exceptions\ActivationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VerisureClientLogoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test can logout from a session
     *
     * @return void
     */
    public function testLogout()
    {
        // Create a valid session
        $this->createSession();

        $response = new Response(302, []);
        $guzzleClient = $this->mockGuzzle($response);
        $client = new VerisureClient($guzzleClient);
        $client->logout();

        $this->assertNotNull($client->getSession()->deleted_at);
    }

    /**
     * Test an exception is triggered if the response is not 201
     *
     * @return void
     */
    public function testLogoutFails()
    {
        $this->expectException(LogoutException::class);
        // Create a valid session
        $this->createSession();

        $response = new Response(200, []);
        $guzzleClient = $this->mockGuzzle($response);
        $client = new VerisureClient($guzzleClient);
        $client->logout();
    }

    /**
     * TearDown the test
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
