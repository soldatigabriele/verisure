<?php

namespace Tests\Unit\VerisureClient;

use App\Record;
use Tests\TestCase;
use App\VerisureClient;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class VerisureClientJobStatusTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Test the job status
     *
     * @return void
     */
    public function testJobStatus()
    {
        $this->createSession();
        $message = $this->callJobStatus(['working', 'queued', 'completed'], ["message" => ["status" => "0", "message" => $alarmMessage = "Your Alarm has been deactivated"]]);

        $this->assertEquals('completed', $message['status']);
        $this->assertEquals(3, \App\Response::count());
        $this->assertEquals($alarmMessage, Record::first()->body);
        $this->assertEquals('job status', \App\Response::latest()->first()->request_type);
    }

    /**
     * Test the responses are censured before logging
     *
     * @return void
     */
    public function testLogStatusResponseCensured()
    {
        $this->createSession();
        $this->callJobStatus(['completed'], [
            "options" => ["user" => "{ some_extra_content_here }"], "name" => "RemoteConnectNightWorker( some_extra_content_here ) ", "message" => ["message" => "foo"],
        ]);

        $this->assertTrue((bool) \App\Response::count());
        $this->assertContains('...', json_encode(\App\Response::latest()->first()->body));
        $this->assertNotContains('some_extra_content_here', json_encode(\App\Response::latest()->first()->body));
    }

    /**
     * Test the responses are not censured if config is off
     *
     * @return void
     */
    public function testLogStatusResponseUncensored()
    {
        config()->set(['verisure.settings.censure_responses' => false]);
        $this->createSession();
        $this->callJobStatus(['completed'], [
            "options" => ["user" => "{ some_extra_content_here }"], "name" => "RemoteConnectNightWorker( some_extra_content_here ) ", "message" => ["message" => "foo"],
        ]);

        $this->assertTrue((bool) \App\Response::count());
        $this->assertContains('some_extra_content_here', json_encode(\App\Response::latest()->first()->body));
    }
}
