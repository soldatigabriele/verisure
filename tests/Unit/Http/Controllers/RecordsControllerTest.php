<?php

namespace Tests\Unit\Http\Controllers;

use App\Status;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class RecordsControllerTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Test get the latest recorded status
     *
     * @return void
     */
    public function testGet()
    {
        $this->withoutMiddleware();
        $status = Status::first();
        $status->update(["house" => 0, "garage" => 1]);

        $response = $this->json('get', '/api/records')->assertStatus(200);
        $this->assertEquals(json_decode($response->getContent())->house, 0);
        $this->assertEquals(json_decode($response->getContent())->garage, 1);
        $this->assertEquals(json_decode($response->getContent())->age, $status->updated_at->timestamp);
    }

    /**
     * Test get the latest recorded status formatted for humans
     *
     * @return void
     */
    public function testGetForHumans()
    {
        $this->withoutMiddleware();
        $status = Status::first();
        $status->update(["house" => 0, "garage" => 1]);

        $response = $this->json('get', '/api/records', ['format' => 'for_humans'])->assertStatus(200);

        $this->assertEquals(json_decode($response->getContent())->message, "House: OFF Garage: ON");
        $this->assertEquals(json_decode($response->getContent())->age, $status->updated_at->diffForHumans());
    }
}
