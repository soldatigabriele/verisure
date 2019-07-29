<?php

namespace Tests\Unit;

use App\Session;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SessionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test session expired
     *
     * @return void
     */
    public function testExpiredSession()
    {
        $session = Session::create([
            'csrf' => Str::random(20),
            'value' => Str::random(20),
            'expires' => Carbon::yesterday(),
        ]);
        $this->assertTrue($session->isExpired());
        $this->assertFalse($session->isValid());
    }

    /**
     * Test the session is valid
     *
     * @return void
     */
    public function testValidSession()
    {
        $session = Session::create([
            'csrf' => Str::random(20),
            'value' => Str::random(20),
            'expires' => Carbon::tomorrow(),
        ]);
        $this->assertFalse($session->isExpired());
        $this->assertTrue($session->isValid());
    }

    /**
     * Test session invalid created_at too old
     *
     * @return void
     */
    public function testTooOldSession()
    {
        // Create a session with a date before the TTL of the session
        Session::unguard();
        $session = Session::create([
            'csrf' => Str::random(20),
            'value' => Str::random(20),
            'expires' => Carbon::tomorrow(),
            'created_at' => Carbon::now()->subMinutes(config('verisure.session.ttl') + 1),
        ]);
        $this->assertTrue($session->isExpired());

        // This one should be Valid
        $session = Session::create([
            'csrf' => Str::random(20),
            'value' => Str::random(20),
            'expires' => Carbon::tomorrow(),
            'created_at' => Carbon::now()->subMinutes(config('verisure.session.ttl') - 1),
        ]);
        $this->assertTrue($session->isValid());
    }
}
