<?php

namespace Tests\Unit\Http\Controllers;

use App\Record;
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
        $record = factory(Record::class)->create();

        $response = $this->json('get', '/api/records')->assertStatus(200);
        $this->assertEquals(json_decode($response->getContent())->message, $record->body);
        $this->assertEquals(json_decode($response->getContent())->age, $record->created_at->diffForHumans());
    }
}
