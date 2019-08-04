<?php

namespace Tests\Unit\Http\Controllers;

use App\User;
use App\Setting;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class SettingsControllerTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Test settings can be updated
     *
     * @return void
     */
    public function testPost()
    {
        $this->actingAs(factory(User::class)->create());
        $setting = factory(Setting::class)->create();
        $this->json('post', 'settings', ['key' => $setting->key, 'value' => !$setting->value])->assertStatus(200);

        $this->assertTrue(!$setting->value == $setting->fresh()->value);
    }

    /**
     * Test settings can be retrieved
     *
     * @return void
     */
    public function testGet()
    {
        $this->actingAs(factory(User::class)->create());
        $settings = factory(Setting::class, 4)->create();
        $response = $this->json('get', 'settings')->assertStatus(200);
        $responseSettings = collect(json_decode($response->getContent()));

        foreach ($settings as $setting) {
            $this->assertTrue($responseSettings->contains('key', $setting->key));
        }
    }

    /**
     * Test settings are loaded in the config on boot
     *
     * @return void
     */
    public function testBoot()
    {
        $this->actingAs(factory(User::class)->create());
        factory(Setting::class)->create(['key' => 'word.test', 'value' => 'value-test']);
        // Load the custom configuration from the DB
        config(['verisure.settings' => load_custom_config()]);

        $this->assertEquals(config('verisure.settings.word.test'), 'value-test');
    }
}
