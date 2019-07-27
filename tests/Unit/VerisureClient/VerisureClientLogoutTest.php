<?php

namespace Tests\Unit\VerisureClient;

use App\Session;
use Tests\TestCase;
use App\VerisureClient;
use GuzzleHttp\Psr7\Response;
use App\Exceptions\LogoutException;
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
        $session = $this->createSession();

        $response = new Response(302, []);
        $guzzleClient = $this->mockGuzzle($response);
        $client = new VerisureClient($guzzleClient);
        $client->logout();

        $this->assertNotNull($session->fresh()->deleted_at);
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
}
