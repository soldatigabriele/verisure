<?php

namespace Tests\Unit;

use Mockery;
use App\Session;
use Carbon\Carbon;
use Tests\TestCase;
use GuzzleHttp\Client;
use App\VerisureClient;
use Illuminate\Support\Str;
use GuzzleHttp\Psr7\Response;
use App\Exceptions\LoginException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VerisureClientLoginTest extends TestCase
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
        $this->assertNull(Session::first());

        // We should do the login if we have an expired token in the DB
        Session::create([
            'csrf' => Str::random(20),
            'value' => Str::random(20),
            'expires' => Carbon::yesterday(),
        ]);

        // The first request is to the login page with the form, the second is the actual login
        $responses = [
            new Response(200, [], $this->getLoginPageHTML()),
            new Response(200, ['Set-Cookie' => '_session_id=test-session-id; path=/; expires=Thu, 04-Jul-2019 20:24:57 GMT; HttpOnly'], $this->getDashboardPageHTML()),
        ];
        $guzzleClient = $this->mockGuzzle($responses);
        (new VerisureClient($guzzleClient))->login();

        // The CSRF Token should be stored in the DB with the session
        $this->assertDatabaseHas('sessions', [
            'value' => 'test-session-id',
            'csrf' => '8cwrVwerJDxZX13dbTYFu6poc050jqqVJDYgplcNPSU=',
        ]);
        $this->assertEquals(1, \App\Response::count());
    }

    /**
     * Login test.
     *
     * @return void
     */
    public function testLoginFails()
    {
        $this->expectException(LoginException::class);

        $responses = [
            new Response(200, [], $this->getLoginPageHTML()),
            new Response(200, ['Set-Cookie' => 'wrong_cookie=test-session-id; path=/; expires=Thu, 04-Jul-2019 20:24:57 GMT; HttpOnly'], $this->getDashboardPageHTML()),
        ];
        $guzzleClient = $this->mockGuzzle($responses);
        (new VerisureClient($guzzleClient))->login();

        $this->assertDatabaseHas('session_cookies', ['value' => 'test-session-id']);
    }

    /**
     * Login test with already a valid session cookie.
     *
     * @return void
     */
    public function testLoginWithSession()
    {
        $session = Session::create([
            'csrf' => Str::random(20),
            'value' => Str::random(20),
            'expires' => Carbon::tomorrow(),
        ]);
        $oldCount = Session::count();

        $spy = Mockery::spy(Client::class);
        $verisure = new VerisureClient($spy);

        $spy->shouldNotHaveReceived('send');
        $this->assertTrue(Session::latest()->first()->is($session));
        $this->assertEquals(Session::count(), $oldCount);
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
