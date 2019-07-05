<?php

namespace Tests\Unit;

use Mockery;
use Carbon\Carbon;
use Tests\TestCase;
use App\SessionCookie;
use GuzzleHttp\Client;
use App\VerisureClient;
use Illuminate\Support\Str;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VerisureClientTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Login test.
     *
     * @return void
     */
    public function testLogin()
    {
        // Check that we don't have any session cookie
        $this->assertNull(SessionCookie::first());
        // We should do the login if we have an expired token in the DB
        SessionCookie::create([
            'value' => Str::random(20),
            'expires' => Carbon::yesterday(),
        ]);

        // The Authenticity Token should be cached
        Cache::shouldReceive('forever')->once()->with('authenticityToken', '8cwrVwerJDxZX13dbTYFu6poc050jqqVJDYgplcNPSU=');

        $loginResponse = new Response(200, [], $this->getLoginPageHTML());
        $dashboardResponse = new Response(200, ['Set-Cookie' => '_session_id=test-session-id; path=/; expires=Thu, 04-Jul-2019 20:24:57 GMT; HttpOnly'], $this->getDashboardPageHTML());

        // Create a mock and queue two responses.
        $mock = new MockHandler([$loginResponse, $dashboardResponse]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['cookies' => true, 'handler' => $handler]);

        $verisure = new VerisureClient([], $client);
        $verisure->login();

        $this->assertDatabaseHas('session_cookies', ['value' => 'test-session-id']);
    }

    /**
     * Login test with already a valid session cookie.
     *
     * @return void
     */
    public function testLoginWithSessionCookie()
    {
        SessionCookie::create([
            'value' => Str::random(20),
            'expires' => Carbon::tomorrow(),
        ]);

        $spy = Mockery::spy(Client::class);
        $verisure = new VerisureClient([], $spy);

        $spy->shouldNotHaveReceived('send');
        $this->assertTrue($verisure->login());
    }

    /**
     * TearDown the test
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    /**
     * Return a fake login page
     *
     * @return void
     */
    public function getLoginPageHTML()
    {
        return ' <!DOCTYPE html> <html lang="gb" > <head></head> <body> <form accept-charset="UTF-8" action="/gb/login/gb" class="login_form form" data-validate-form="" id="new_verisure_rsi_login" method="post" novalidate="novalidate"><div style="margin:0;padding:0;display:inline"><input name="utf8" type="hidden" value="&#x2713;" /><input name="authenticity_token" type="hidden" value="8cwrVwerJDxZX13dbTYFu6poc050jqqVJDYgplcNPSU=" /></div> <fieldset style="border:0px !important"> <span class="input_wrapp"><input class="required" id="verisure_rsi_login_nick" name="verisure_rsi_login[nick]" size="30" type="text" /></span> <span class="input_wrapp"><input class="required" id="verisure_rsi_login_passwd" name="verisure_rsi_login[passwd]" size="30" type="password" /></span> <button class="m_btn m_btn_std" id="button_login" name="button" type="submit">Start Session</button> </form> </body> </html>';
    }

    /**
     * Return a fake Dashboard page
     *
     * @return void
     */
    public function getDashboardPageHTML()
    {
        return ' <!DOCTYPE html> <html lang="gb" > <head></head> <body> <form accept-charset="UTF-8" action="/gb/login/gb" class="login_form form" data-validate-form="" id="new_verisure_rsi_login" method="post" novalidate="novalidate"><div style="margin:0;padding:0;display:inline"><input name="utf8" type="hidden" value="&#x2713;" /><input name="authenticity_token" type="hidden" value="8cwrVwerJDxZX13dbTYFu6poc050jqqVJDYgplcNPSU=" /></div> <fieldset style="border:0px !important"> <span class="input_wrapp"><input class="required" id="verisure_rsi_login_nick" name="verisure_rsi_login[nick]" size="30" type="text" /></span> <span class="input_wrapp"><input class="required" id="verisure_rsi_login_passwd" name="verisure_rsi_login[passwd]" size="30" type="password" /></span> <button class="m_btn m_btn_std" id="button_login" name="button" type="submit">Start Session</button> </form> </body> </html>';
    }
}
