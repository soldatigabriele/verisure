<?php

namespace Tests\Unit\VerisureClient;

use Exception;
use App\Session;
use Tests\TestCase;
use App\VerisureClient;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class VerisureClientLogoutTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Test can logout from a session
     *
     * @return void
     */
    public function testLogout()
    {
        // Create a valid session
        $sessionOne = $this->createSession();
        $sessionTwo = $this->createSession();

        $response = new Response(200, []);
        $guzzleClient = $this->mockGuzzle($response);
        $client = new VerisureClient($guzzleClient);
        $client->logout();

        $this->assertNotNull($sessionOne->fresh()->deleted_at);
        $this->assertNotNull($sessionTwo->fresh()->deleted_at);
    }

    /**
     * Test an exception is triggered if the response is not 201
     *
     * @return void
     */
    public function testLogoutFails()
    {
        $this->expectException(Exception::class);
        // Create a valid session
        $this->createSession();

        $response = new Response(404, []);
        $guzzleClient = $this->mockGuzzle($response);
        $client = new VerisureClient($guzzleClient);
        $client->logout();
    }
}
