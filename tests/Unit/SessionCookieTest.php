<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Tests\TestCase;
use App\SessionCookie;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SessionCookieTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Login test.
     *
     * @return void
     */
    public function testExpiredCookie()
    {
        // Check that we don't have any session cookie
        $session = SessionCookie::create([
            'value' => Str::random(20),
            'expires' => Carbon::yesterday(),
        ]);
        $this->assertTrue($session->isExpired());
        $this->assertFalse($session->isValid());

    }
    public function testValidCookie()
    {
        $session = SessionCookie::create([
            'value' => Str::random(20),
            'expires' => Carbon::tomorrow(),
        ]);
        $this->assertFalse($session->isExpired());
        $this->assertTrue($session->isValid());
    }
}
