<?php

namespace Tests\Unit\Jobs;

use Mockery;
use App\Jobs\Login;
use Tests\TestCase;
use App\VerisureClient;

class LoginTest extends TestCase
{
    /**
     * Test the Login job calls the login method of VerisureClient
     */
    public function testLogin()
    {
        $mock = Mockery::mock(VerisureClient::class)
            ->shouldReceive('login')->getMock();

        // Dispatch the job
        (new Login)->handle($mock);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
