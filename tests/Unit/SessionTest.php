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
     * Login test.
     *
     * @return void
     */
    public function testExpiredSession()
    {
        // Check that we don't have any session
        $session = Session::create([
            'csrf' => Str::random(20),
            'value' => Str::random(20),
            'expires' => Carbon::yesterday(),
        ]);
        $this->assertTrue($session->isExpired());
        $this->assertFalse($session->isValid());

    }
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
}
