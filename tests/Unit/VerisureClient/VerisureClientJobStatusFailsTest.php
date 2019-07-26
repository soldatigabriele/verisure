<?php

namespace Tests\Unit\VerisureClient;

use App\Record;
use Tests\TestCase;
use App\VerisureClient;
use GuzzleHttp\Psr7\Response;
use App\Exceptions\JobStatusException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VerisureClientJobStatusFailsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test a failed attempt to activate alarm (window open)
     *
     * @return void
     */
    public function testJobStatusFailsOpenWindow()
    {
        $this->createSession();
        $message = $this->callJobStatus(['working', 'failed'], ["message" => $alarmMessage = "Unable to connect the Alarm. One zone is open, check your windows and/or doors and try again."]);

        $this->assertEquals('failed', $message['status']);
        $this->assertEquals(2, \App\Response::count());
        $this->assertEquals($alarmMessage, Record::first()->body);
    }

    /**
     * Test an exception is triggered if the status in the response is not 'completed' or 'working'
     *
     * @return void
     */
    public function testJobStatusFailsForWrongStatus()
    {
        $this->createSession();
        $this->expectException(JobStatusException::class);

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
        $this->expectException(JobStatusException::class);
        for ($i = 0; $i < config('verisure.status_job.max_calls') + 1; $i++) {
            $statuses[] = "working";
        }
        // Calle the jobStatus method on the VerisureClient
        $this->callJobStatus($statuses);
    }
}
