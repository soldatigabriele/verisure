<?php

namespace App;

use App\Session;
use DOMDocument;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use \App\Exceptions\LoginException;
use Illuminate\Support\Facades\Cache;

class VerisureClient
{
    protected $client;

    /**
     * Constructor of the client
     *
     * @param Client $client
     */
    public function __construct(Client $client = null)
    {
        $this->client = $client ?? (new Client(['cookies' => true]));
    }

    /**
     * Login into the service
     *
     * @return bool
     */
    public function login(): Session
    {
        // If we have a valid cookie, there is no need to log in again
        if (optional($session = Session::latest()->first())->isValid()) {
            return $session;
        }

        // Get the Authenticity Token from the login page (CSRF token)
        if ($session = $this->getAuthenticityToken()) {
            $loginRequest = new Request(
                "POST",
                config("verisure.url"). "/gb/login/gb",
                [
                    "Origin" => "https://customers.verisure.co.uk",
                    "Connection" => "keep-alive",
                    "Content-Type" => "application/x-www-form-urlencoded",
                    "Cache-Control" => "max-age=0",
                    "Upgrade-Insecure-Requests" => "1",
                    "User-Agent" => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36",
                    "Referer" => "https://customers.verisure.co.uk/",
                    "Accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3",
                    "Accept-Encoding" => "gzip, deflate, br",
                    "Accept-Language" => "en-GB,en;q=0.9,it-IT;q=0.8,it;q=0.7,en-US;q=0.6",
                ],
                "utf8=%E2%9C%93&authenticity_token=" . $session->csrf . "&verisure_rsi_login%5Bnick%5D=" . config("verisure.username") . "&verisure_rsi_login%5Bpasswd%5D=" . config("verisure.password") . "&button="
            );
            $this->client->send($loginRequest);
            
            // Store the session cookie
            $cookieJar = $this->client->getConfig('cookies');
            $cookie = $cookieJar->getCookieByName('_session_id');
            if ($cookie) {
                $session->value =  $cookie->getValue();
                $session->expires = Carbon::createFromTimestamp($cookie->getExpires());
                $session->save();
                return $session;
            }
            throw new LoginException("Session cookie was not returned after the login");
        }
        throw new LoginException("Error during the login process");
    }

    public function logout()
    {

    }

    public function activate()
    {

    }

    public function deactivate()
    {

    }

    public function status()
    {
        $sessionId = Session::latest('id')->firstOrFail();

        // TODO change logic and test
        if ($sessionId->expires < Carbon::now() || is_null(Cache::get('authenticityToken'))) {
            $this->login();
        }

        $request = new Request(
            "POST",
            config("verisure.url"). "/gb/installations/".config("verisure.installation")."/panel/status",
            [
                "Cookie" => "accept_cookies=1; _session_id=" . $sessionId->value,
                "Origin" => "https://customers.verisure.co.uk",
                "Accept-Encoding" => "gzip, deflate, br",
                "X-Csrf-Token" => Cache::get('authenticityToken'),
                "Accept-Language" => "en-GB,en;q=0.9,it-IT;q=0.8,it;q=0.7,en-US;q=0.6",
                "User-Agent" => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36",
                "Content-Type" => "application/x-www-form-urlencoded; charset=UTF-8",
                "Accept" => "application/json, text/javascript, */*; q=0.01",
                "Referer" => "https://customers.verisure.co.uk/gb/installations",
                "X-Requested-With" => "XMLHttpRequest",
                "Connection" => "keep-alive",
            ],
            "utf8=%E2%9C%93&authenticity_token=" . Cache::get('authenticityToken'));

        $response = $this->client->send($request);
        $response = (string) $response->getBody();
        dump($response->getStatusCode());
        if ($response->getStatusCode() == 201) {
            return json_decode($response)->job_id;
        }
    }

    public function refreshSession()
    {

    }

    /**
     * Get the Authenticity Token from the login page
     *
     * @return Session
     */
    protected function getAuthenticityToken(): Session
    {
        $request = new Request(
            "GET",
            config("verisure.url"). "/gb/login/gb",
            [
                "Connection" => "keep-alive",
                "Cache-Control" => "max-age=0",
                "Upgrade-Insecure-Requests" => "1",
                "User-Agent" => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36",
                "Accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3",
                "Accept-Encoding" => "gzip, deflate, br",
                "Accept-Language" => "en-GB,en;q=0.9,it-IT;q=0.8,it;q=0.7,en-US;q=0.6",
            ],
            "");

        $response = $this->client->send($request);

        // Parse the HTML from the response and get the authenticity_token
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML((string) $response->getBody()->getContents());
        libxml_clear_errors();
        foreach ($dom->getElementsByTagName('input') as $input) {
            if ($input->getAttribute('name') == 'authenticity_token') {
                return Session::create([
                    'csrf' => $input->getAttribute('value'),
                ]);
            }
        }

        throw new \Exception("Autenticity Token not found");
    }
}
