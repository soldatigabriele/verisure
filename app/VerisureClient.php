<?php

namespace App;

use App\Client;
use App\Session;
use DOMDocument;
use Carbon\Carbon;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\ClientInterface;
use \App\Exceptions\LoginException;
use App\Exceptions\LogoutException;
use App\Exceptions\StatusException;
use Illuminate\Support\Facades\Cache;
use App\Exceptions\ActivationException;
use App\Exceptions\DeactivationException;

class VerisureClient
{
    protected $client;

    protected $session;

    /**
     * Constructor of the client
     *
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client = null)
    {
        $this->client = $client ?? (new Client(['cookies' => true]));

        // If we have a valid cookie, there is no need to login again
        if (optional($session = Session::latest()->first())->isValid()) {
            $this->session = $session;
            return $this;
        }

        // Get the Authenticity Token from the login page (CSRF token)
        if ($this->session = $this->getAuthenticityToken()) {
            $loginRequest = new Request(
                "POST",
                config("verisure.url") . "/gb/login/gb",
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
                "utf8=%E2%9C%93&authenticity_token=" . $this->session->csrf . "&verisure_rsi_login%5Bnick%5D=" . config("verisure.username") . "&verisure_rsi_login%5Bpasswd%5D=" . config("verisure.password") . "&button="
            );
            $this->client->send($loginRequest);

            // Store the session cookie
            $cookieJar = $this->client->getConfig('cookies');
            $cookie = $cookieJar->getCookieByName('_session_id');
            if ($cookie) {
                $this->session->value = $cookie->getValue();
                $this->session->expires = Carbon::createFromTimestamp($cookie->getExpires());
                $this->session->save();
                return $this;
            }
            throw new LoginException("Session cookie was not returned after the login");
        }
        throw new LoginException("Error during the login process");
    }

    /**
     * Return the current active session
     *
     * @return Session
     */
    public function getSession(): Session
    {
        return $this->session;
    }

    /**
     * Logout and invalidate the current Session
     *
     * @return string
     */
    public function logout()
    {
        if ($this->session->isValid()) {
            $request = new Request(
                "GET",
                config("verisure.url") . "/gb/logout",
                [
                    "Cookie" => "accept_cookies=1; _session_id=" . $this->session->value,
                    "Origin" => "https://customers.verisure.co.uk",
                    "Accept-Encoding" => "gzip, deflate, br",
                    "X-Csrf-Token" => $this->session->csrf,
                    "Accept-Language" => "en-GB,en;q=0.9,it-IT;q=0.8,it;q=0.7,en-US;q=0.6",
                    "User-Agent" => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36",
                    "Content-Type" => "application/x-www-form-urlencoded; charset=UTF-8",
                    "Accept" => "application/json, text/javascript, */*; q=0.01",
                    "Referer" => "https://customers.verisure.co.uk/gb/installations",
                    "X-Requested-With" => "XMLHttpRequest",
                    "Connection" => "keep-alive",
                ]);

            // Guzzle will throw an exception if the response is not in the 2xx
            $response = $this->client->send($request);
            if ($response->getStatusCode() == 302) {
                // Delete the session
                $this->session->delete();
                return json_decode($response->getBody()->getContents());
            }
            throw new LogoutException("Server responded with status code: " . $response->getStatusCode());
        }
        return "Your session is already expired";
    }


    /**
     * Activate the main alarm
     */
    public function activate(string $mode = null)
    {
        $mode = in_array($mode, ['house', 'night', 'day']) ? $mode : 'house';
        $request = new Request(
            "POST",
            config("verisure.url") . "/gb/installations/" . config("verisure.installation") . "/panel/" . $mode,
            [
                "Cookie" => "accept_cookies=1; _session_id=" . $this->session->value,
                "Origin" => "https://customers.verisure.co.uk",
                "Accept-Encoding" => "gzip, deflate, br",
                "X-Csrf-Token" => $this->session->csrf,
                "Accept-Language" => "en-GB,en;q=0.9,it-IT;q=0.8,it;q=0.7,en-US;q=0.6",
                "User-Agent" => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36",
                "Content-Type" => "application/x-www-form-urlencoded; charset=UTF-8",
                "Accept" => "application/json, text/javascript, */*; q=0.01",
                "Referer" => "https://customers.verisure.co.uk/gb/installations",
                "X-Requested-With" => "XMLHttpRequest",
                "Connection" => "keep-alive",
            ],
            "utf8=%E2%9C%93&authenticity_token=" . $this->session->csrf);

        // Guzzle will throw an exception if the response is not in the 2xx
        $response = $this->client->send($request);
        if ($response->getStatusCode() == 201) {
            return json_decode($response->getBody()->getContents())->job_id;
        }
        throw new ActivationException("Server responded with status code: " . $response->getStatusCode());
    }

    /**
     * Deactivate the main alarm
     */
    public function deactivate()
    {
        $request = new Request(
            "POST",
            config("verisure.url") . "/gb/installations/" . config("verisure.installation") . "/panel/unlock",
            [
                "Cookie" => "accept_cookies=1; _session_id=" . $this->session->value,
                "Origin" => "https://customers.verisure.co.uk",
                "Accept-Encoding" => "gzip, deflate, br",
                "X-Csrf-Token" => $this->session->csrf,
                "Accept-Language" => "en-GB,en;q=0.9,it-IT;q=0.8,it;q=0.7,en-US;q=0.6",
                "User-Agent" => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36",
                "Content-Type" => "application/x-www-form-urlencoded; charset=UTF-8",
                "Accept" => "application/json, text/javascript, */*; q=0.01",
                "Referer" => "https://customers.verisure.co.uk/gb/installations",
                "X-Requested-With" => "XMLHttpRequest",
                "Connection" => "keep-alive",
            ],
            "utf8=%E2%9C%93&authenticity_token=" . $this->session->csrf);

        // Guzzle will throw an exception if the response is not in the 2xx
        $response = $this->client->send($request);
        if ($response->getStatusCode() == 201) {
            return json_decode($response->getBody()->getContents())->job_id;
        }
        throw new DeactivationException("Server responded with status code: " . $response->getStatusCode());
    }

    public function status()
    {
        $request = new Request(
            "POST",
            config("verisure.url") . "/gb/installations/" . config("verisure.installation") . "/panel/status",
            [
                "Cookie" => "accept_cookies=1; _session_id=" . $this->session->value,
                "Origin" => "https://customers.verisure.co.uk",
                "Accept-Encoding" => "gzip, deflate, br",
                "X-Csrf-Token" => $this->session->csrf,
                "Accept-Language" => "en-GB,en;q=0.9,it-IT;q=0.8,it;q=0.7,en-US;q=0.6",
                "User-Agent" => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36",
                "Content-Type" => "application/x-www-form-urlencoded; charset=UTF-8",
                "Accept" => "application/json, text/javascript, */*; q=0.01",
                "Referer" => "https://customers.verisure.co.uk/gb/installations",
                "X-Requested-With" => "XMLHttpRequest",
                "Connection" => "keep-alive",
            ],
            "utf8=%E2%9C%93&authenticity_token=" . $this->session->csrf);

        // Guzzle will throw an exception if the response is not in the 2xx
        $response = $this->client->send($request);
        if ($response->getStatusCode() == 201) {
            return json_decode($response->getBody()->getContents())->job_id;
        }
        throw new StatusException("Server responded with status code: " . $response->getStatusCode());
    }

    protected function refreshSession()
    {
        //
    }

    public function jobStatus()
    {
        //
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
            config("verisure.url") . "/gb/login/gb",
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
