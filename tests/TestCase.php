<?php

namespace Tests;

use App\Session;
use GuzzleHttp\Client;
use App\VerisureClient;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * SetUp
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        config()->set([
            "verisure.url" => "http://verisure-example.test",
            "verisure.auth.active" => false,
        ]);
        $uses = array_flip(class_uses_recursive(static::class));
        if (isset($uses[DatabaseMigrations::class])) {
            config(['verisure.settings'=>load_custom_config()]);
        }
    }

    /**
     * Mock the Guzzle Client
     *
     * @param mixed|array $responses
     * @return Client
     */
    public function mockGuzzle($responses): Client
    {
        $responses = is_array($responses) ? $responses : Arr::wrap($responses);

        $mock = new MockHandler($responses);
        $handler = HandlerStack::create($mock);
        return new Client(['cookies' => true, 'handler' => $handler]);
    }

    /**
     * Generate an array of Guzzle Responses
     *
     * @param array $statuses the list of statuses. One per response
     * @param array $extra
     * @return array
     */
    protected function callJobStatus(array $statuses = ["working"], array $extra = []): array
    {
        foreach ($statuses as $status) {
            $content = ["status" => $status];
            if ($status == 'completed' || $status == 'failed') {
                $content = array_merge($content, $extra);
            }
            $responses[] = new Response(200, [], json_encode($content));
        }

        $client = (new VerisureClient($this->mockGuzzle($responses)));
        return $client->jobStatus(Str::random(20));
    }

    /**
     * Create a valid session
     *
     * @return Session
     */
    public function createSession(): Session
    {
        return Session::create([
            'csrf' => Str::random(20),
            'value' => Str::random(20),
            'expires' => now()->addMinutes(20),
        ]);
    }
}
