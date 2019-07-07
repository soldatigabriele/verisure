<?php

namespace Tests;

use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
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
        config()->set(["verisure.url" => "http://verisure-example.test"]);
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
}
