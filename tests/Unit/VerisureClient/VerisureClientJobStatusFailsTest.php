<?php

namespace Tests\Unit\VerisureClient;

use Exception;
use App\Record;
use Tests\TestCase;
use App\VerisureClient;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class VerisureClientJobStatusFailsTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Test a failed attempt to activate alarm (window open)
     *
     * @return void
     */
    public function testJobStatusFailsOpenWindow()
    {
        $session = $this->createSession();
        $message = $this->callJobStatus(['working', 'failed'], ["message" => $alarmMessage = "Unable to connect the Alarm. One zone is open, check your windows and/or doors and try again."]);

        $this->assertEquals('failed', $message['status']);
        $this->assertEquals(2, \App\Response::count());
        $this->assertEquals($alarmMessage, Record::first()->body);
        $this->assertNull($session->deleted_at);
    }

    /**
     * Test a failed attempt to activate alarm (We have had problems identifying you, please end session and log in again.)
     *
     * @return void
     */
    public function testJobStatusFailsIdentificationProblems()
    {
        $session = $this->createSession();
        $this->assertNull($session->deleted_at);
        $this->callJobStatus(['working', 'failed'], ["message" => $alarmMessage = "We have had problems identifying you, please end session and log in again."]);
        // We want to logout and try the job again
        $this->assertNotNull($session->fresh()->deleted_at);
    }

    /**
     * Test an exception is triggered if the status in the response is not 'completed' or 'working'
     *
     * @return void
     */
    public function testJobStatusFailsForWrongStatus()
    {
        $this->createSession();
        $this->expectException(Exception::class);

        // Calle the jobStatus method on the VerisureClient
        $this->callJobStatus(['unknown-status'], ["message" => "foo bar"]);
        $this->assertEquals(1, \App\Response::count());
    }

    /**
     * Test
     *
     * @return void
     */
    public function testJobStatusFailsForTooManyAttempts()
    {
        $this->createSession();
        $this->expectException(Exception::class);
        for ($i = 0; $i < config('verisure.settings.status_job.max_calls') + 1; $i++) {
            $statuses[] = "working";
        }
        // Calle the jobStatus method on the VerisureClient
        $this->callJobStatus($statuses);
    }
}
