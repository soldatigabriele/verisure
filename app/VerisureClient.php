<?php

namespace App;

use App\Client;
use App\Session;
use DOMDocument;
use Carbon\Carbon;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use App\Request as LogRequest;
use GuzzleHttp\ClientInterface;
use App\Response as LogResponse;
use \App\Exceptions\LoginException;
use App\Exceptions\LogoutException;
use App\Exceptions\StatusException;
use App\Exceptions\JobStatusException;
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
    }

    /**
     * Check if we have a valid session, otherwise login
     *
     * @return void
     */
    protected function setSession(): void
    {
        if (optional($this->session = Session::latest()->first())->isValid()) {
            return;
        }
        $this->login();
        return;
    }

    public function login()
    {
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
                "utf8=%E2%9C%93&authenticity_token=" . urlencode($this->session->csrf) . "&verisure_rsi_login%5Bnick%5D=" . config("verisure.username") . "&verisure_rsi_login%5Bpasswd%5D=" . config("verisure.password") . "&button="
            );
            $response = $this->client->send($loginRequest);
            $this->logResponse($response, $response->getBody()->getContents());

            // Store the session cookie
            if ($cookie = $this->client->getConfig('cookies')->getCookieByName('_session_id')) {
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
     * Logout and invalidate the current Session
     *
     * @return string
     */
    public function logout()
    {
        $this->setSession();
        if ($this->session->isValid()) {
            $request = new Request("GET", config("verisure.url") . "/gb/logout", $this->headers());

            // Guzzle will throw an exception if the response is not in the 2xx
            $response = $this->client->send($request);
            $body = $response->getBody()->getContents();
            $this->logResponse($response, $body);
            if ($response->getStatusCode() == 302) {
                // Delete the session
                $this->session->delete();
                return json_decode($body);
            }
            throw new LogoutException("Server responded with status code: " . $response->getStatusCode());
        }
        return "Your session is already expired";
    }

    /**
     * Activate the annex alarm
     */
    public function activateAnnex(string $mode = null)
    {
        $this->setSession();
        $request = new Request(
            "POST",
            config("verisure.url") . "/gb/installations/" . config("verisure.installation") . "/panel/twice",
            $this->headers(),
            "utf8=%E2%9C%93&authenticity_token=" . urlencode($this->session->csrf) . "&typeAnnex=0&typeAnnex=1");

        // Guzzle will throw an exception if the response is not in the 2xx
        $response = $this->client->send($request);
        $body = $response->getBody()->getContents();
        $this->logResponse($response, $body);

        if ($response->getStatusCode() == 201) {
            return json_decode($body)->job_id;
        }
        throw new ActivationException("Server responded with status code: " . $response->getStatusCode());
    }

    /**
     * Deactivate the annex alarm
     */
    public function deactivateAnnex(string $mode = null)
    {
        $this->setSession();
        $request = new Request(
            "POST",
            config("verisure.url") . "/gb/installations/" . config("verisure.installation") . "/panel/twice",
            $this->headers(),
            "utf8=%E2%9C%93&authenticity_token=" . urlencode($this->session->csrf) . "&typeAnnex=0");

        // Guzzle will throw an exception if the response is not in the 2xx
        $response = $this->client->send($request);
        // TODO this $response->getBody() gets unset in the logRespons ??
        $body = $response->getBody()->getContents();
        $this->logResponse($response, $body);

        if ($response->getStatusCode() == 201) {
            return json_decode($body)->job_id;
        }
        throw new DeactivationException("Server responded with status code: " . $response->getStatusCode());
    }

    /**
     * Activate the main alarm
     */
    public function activate(string $mode = null)
    {
        $this->setSession();
        $mode = in_array($mode, ['house', 'night', 'day']) ? $mode : 'house';
        $request = new Request(
            "POST",
            config("verisure.url") . "/gb/installations/" . config("verisure.installation") . "/panel/" . $mode,
            $this->headers(),
            "utf8=%E2%9C%93&authenticity_token=" . urlencode($this->session->csrf));

        // Guzzle will throw an exception if the response is not in the 2xx
        $response = $this->client->send($request);
        $body = $response->getBody()->getContents();
        $this->logResponse($response, $body);

        if ($response->getStatusCode() == 201) {
            return json_decode($body)->job_id;
        }
        throw new ActivationException("Server responded with status code: " . $response->getStatusCode());
    }

    /**
     * Deactivate the main alarm
     */
    public function deactivate()
    {
        $this->setSession();
        $request = new Request(
            "POST",
            config("verisure.url") . "/gb/installations/" . config("verisure.installation") . "/panel/unlock",
            $this->headers(),
            "utf8=%E2%9C%93&authenticity_token=" . urlencode($this->session->csrf));

        // Guzzle will throw an exception if the response is not in the 2xx
        $response = $this->client->send($request);
        $body = $response->getBody()->getContents();
        $this->logResponse($response, $body);

        if ($response->getStatusCode() == 201) {
            return json_decode($body)->job_id;
        }
        throw new DeactivationException("Server responded with status code: " . $response->getStatusCode());
    }

    public function status()
    {
        $this->setSession();
        $request = new Request(
            "POST",
            config("verisure.url") . "/gb/installations/" . config("verisure.installation") . "/panel/status",
            $this->headers(),
            "utf8=%E2%9C%93&authenticity_token=" . urlencode($this->session->csrf));

        // Guzzle will throw an exception if the response is not in the 2xx
        $response = $this->client->send($request);
        $body = $response->getBody()->getContents();
        $this->logResponse($response, $body);

        if ($response->getStatusCode() == 201) {
            return json_decode($body)->job_id;
        }
        throw new StatusException("Server responded with status code: " . $response->getStatusCode());
    }

    protected function refreshSession()
    {
        //
    }

    public function jobStatus(string $jobId)
    {
        $this->setSession();
        $counter = 0;
        $status = "queued";
        while ($status == "working" || $status == "queued") {
            if ($counter > config('verisure.status_job.max_calls')) {
                throw new JobStatusException("Too many attempts");
            }

            $headers = $this->headers();
            unset($headers['Origin']);
            $request = new Request("GET", config("verisure.url") . "/es/remote/job_status/" . $jobId, $headers, "");

            $response = $this->client->send($request);
            $body = $response->getBody()->getContents();
            $this->logResponse($response, $body);
            $response = json_decode($body);

            $status = $response->status;
            $counter++;
            // In production, add a timer between the requests
            if (env("APP_ENV") !== "testing" && $status !== "completed") {
                sleep(config('verisure.status_job.sleep_between_calls'));
            }
        }

        if ($status == "failed") {
            // Note: the message in the failed response is not under ['message']['message']
            return ["status" => $status, "message" => $response->message];
        }
        if ($status == "completed") {
            return ["status" => $status, "message" => $response->message->message];
        }
        throw new JobStatusException("Error in the response: " . $status);
    }

    /**
     * Set the header for the guzzle client
     *
     * @return array
     */
    protected function headers(): array
    {
        return [
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
        ];
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

    /**
     * Log the response from the server
     *
     * @param \GuzzleHttp\Psr7\Response $response
     * @return void
     */
    protected function logResponse(Response $response, $body)
    {
        $log = new LogResponse;
        $log->status = $response->getStatusCode();
        $log->headers = $response->getHeaders();
        $log->body = $body;

        if ($request = LogRequest::latest()->first()) {
            $request->response()->save($log);
        }
        $log->save();
    }
}
