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

        (new VerisureClient)->logout();

        $this->assertNotNull($sessionOne->fresh()->deleted_at);
        $this->assertNotNull($sessionTwo->fresh()->deleted_at);
    }
}
