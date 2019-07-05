<?php

namespace App;

use DOMDocument;
use Carbon\Carbon;
use App\SessionCookie;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Cache;

class VerisureClient
{
    protected $client;
    protected $config;
    protected $authenticityToken;

    /**
     * Constructor of the client
     *
     * @param array $config
     * @param Client $client
     */
    public function __construct(array $config = [], Client $client = null)
    {
        $this->config = $config;
        $this->client = $client ?? (new Client(['cookies' => true]));
    }

    /**
     * Login into the service
     *
     * @return bool
     */
    public function login(): bool
    {
        // If we have a valid cookie, there is no need to log in again
        if (optional(SessionCookie::latest()->first())->isValid()) {
            return true;
        }

        // Get the Authenticity Token from the login page (CSRF token)
        $this->getAuthenticityToken();

        if ($this->authenticityToken) {
            $loginRequest = new Request(
                "POST",
                "https://customers.verisure.co.uk/gb/login/gb",
                // "http://verisure-example.test/login.php",
                [
                    // "Origin" => "https://customers.verisure.co.uk",
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
                "utf8=%E2%9C%93&authenticity_token=" . $this->authenticityToken . "&verisure_rsi_login%5Bnick%5D=" . env("VERISURE_APP_USERNAME") . "&verisure_rsi_login%5Bpasswd%5D=" . env("VERISURE_APP_PASSWORD") . "&button="
            );

            $this->client->send($loginRequest);

            // Store the session cookie
            $cookieJar = $this->client->getConfig('cookies');
            $sessionCookie = $cookieJar->getCookieByName('_session_id');
            if ($sessionCookie) {
                SessionCookie::create([
                    'value' => $sessionCookie->getValue(),
                    'expires' => Carbon::createFromTimestamp($sessionCookie->getExpires()),
                ]);
            }

            return true;
        }
        throw new \Exception("Error during the login process");
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
        $sessionId = SessionCookie::latest('id')->firstOrFail();

        // TODO change logic and test
        if ($sessionId->expires < Carbon::now() || is_null(Cache::get('authenticityToken'))) {
            $this->login();
        }

        $request = new Request(
            "POST",
            "https://verisure-example.test/gb/installations/243397/panel/status",
            // "https://customers.verisure.co.uk/gb/installations/243397/panel/status",
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
     * @return bool
     */
    protected function getAuthenticityToken(): bool
    {
        $request = new Request(
            "GET",
            "https://customers.verisure.co.uk/gb/login/gb",
            // "http://verisure-example.test/login.html",
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

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML((string) $response->getBody()->getContents());
        libxml_clear_errors();
        foreach ($dom->getElementsByTagName('input') as $input) {
            if ($input->getAttribute('name') == 'authenticity_token') {
                $this->authenticityToken = $input->getAttribute('value');
                Cache::forever('authenticityToken', $this->authenticityToken);
                return true;
            }
        }

        throw new \Exception("Autenticity Token not found");
    }
}
