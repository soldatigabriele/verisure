<?php

namespace Tests\Unit\Jobs;

use Mockery;
use Tests\TestCase;
use App\Jobs\Status;
use App\Jobs\CallWebhook;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class CallWebhookTest extends TestCase
{
    /**
     * Test Status job
     *
     * @return void
     */
    public function testWebhookIsCalled()
    {
        $guzzleClient = $this->mockGuzzle(new Response(200, []));
        (new CallWebhook("test", ["test"]))->handle($guzzleClient);
        $this->addToAssertionCount(1);
    }
}
