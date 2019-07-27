<?php

namespace App;

use Exception;
use App\Client;
use App\Record;
use App\Session;
use DOMDocument;
use Carbon\Carbon;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use App\Request as LogRequest;
use GuzzleHttp\ClientInterface;
use App\Response as LogResponse;
use GuzzleHttp\Cookie\SetCookie;
use \App\Exceptions\LoginException;
use App\Exceptions\LogoutException;

class VerisureClient
{
    /**
     * Instance of ClientInterface
     *
     * @var ClientInterface
     */
    protected $client;

    /**
     * Instance of Session
     *
     * @var Session
     */
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
     * @return bool
     */
    protected function setSession(): void
    {
        if (optional($this->session = Session::latest('id')->first())->isValid()) {
            return;
        }
        $this->login();
        return;
    }

    /**
     * Login and store the Session
     *
     * @return $this
     */
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
                $this->storeSessionCookie($cookie);
                return $this;
            }
            throw new LoginException("Session cookie was not returned after the login");
        }
        throw new LoginException("Error during the login process");
    }

    /**
     * Store the session cookie in the Database
     *
     * @param SetCookie $cookie
     * @return bool
     */
    protected function storeSessionCookie(SetCookie $cookie)
    {
        $this->session->value = $cookie->getValue();
        $this->session->expires = Carbon::createFromTimestamp($cookie->getExpires());
        return $this->session->save();
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
     *
     * @return string $job_id the Id of the current job
     */
    public function activateAnnex()
    {
        return $this->request('POST', 'panel/twice', '&typeAnnex=0&typeAnnex=1');
    }

    /**
     * Deactivate the annex alarm
     *
     * @return string $job_id the Id of the current job
     */
    public function deactivateAnnex()
    {
        return $this->request('POST', 'panel/twice', '&typeAnnex=0');
    }

    /**
     * Activate the main alarm
     *
     * @return string $job_id the Id of the current job
     */
    public function activate(string $mode)
    {
        return $this->request('POST', 'panel/' . $mode);
    }

    /**
     * Deactivate the main alarm
     *
     * @return string $job_id the Id of the current job
     */
    public function deactivate()
    {
        return $this->request('POST', 'panel/unlock');
    }

    /**
     * Check for the status of the alarm
     *
     * @return $this
     */
    public function status()
    {
        return $this->request('POST', 'panel/status');
    }

    /**
     * Process the job status
     *
     * @param string $jobId
     * @return array
     */
    public function jobStatus(string $jobId)
    {
        $this->setSession();
        $counter = 0;
        $status = "queued";
        while ($status == "working" || $status == "queued") {
            if ($counter > config('verisure.status_job.max_calls')) {
                throw new Exception("Too many attempts");
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
            Record::create(['body' => $response->message]);
            return ["status" => $status, "message" => $response->message];
        }
        if ($status == "completed") {
            Record::create(['body' => $response->message->message]);
            return ["status" => $status, "message" => $response->message->message];
        }
        throw new Exception("Error in the response: " . $status);
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
     * Make a Guzzle request to the specified endpoint
     *
     * @param string $method
     * @param string $endpoint
     * @param string $options optional body parameters
     * @return string $job_id the Id of the current job
     */
    protected function request(string $method, string $endpoint, string $options = ""): string
    {
        $this->setSession();
        $request = new Request(
            $method,
            config("verisure.url") . "/gb/installations/" . config("verisure.installation") . "/" . $endpoint,
            $this->headers(),
            "utf8=%E2%9C%93&authenticity_token=" . urlencode($this->session->csrf) . $options);

        // Guzzle will throw an exception if the response is not in the 2xx
        $response = $this->client->send($request);

        // Update the session cookie
        if ($cookie = $this->client->getConfig('cookies')->getCookieByName('_session_id')) {
            $this->storeSessionCookie($cookie);
        }

        $body = $response->getBody()->getContents();
        $this->logResponse($response, $body);

        if ($response->getStatusCode() == 201) {
            return json_decode($body)->job_id;
        }
        throw new Exception("Server responded with status code: " . $response->getStatusCode());
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

        $body = json_decode($body, true);
        if (config('verisure.censure_responses')) {
            // Note: we remove the errors and the content we don't need as they
            // were returning a bunch of extra heavy data for every response
            $body['options']['user'] = 'CONTENT REMOVED';
            $body['name'] = 'CONTENT REMOVED';
        }

        // TODO this $response->getBody() gets unset in the logRespons ?? This is why we need to pass the response and body

        $log->status = $response->getStatusCode();
        $log->headers = $response->getHeaders();
        $log->body = $body;

        if ($request = LogRequest::latest('id')->first()) {
            $request->response()->save($log);
        }
        $log->save();
    }
}
