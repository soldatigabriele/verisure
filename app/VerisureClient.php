<?php

namespace App;

use Exception;
use App\Client;
use App\Record;
use App\Session;
use DOMDocument;
use Carbon\Carbon;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\SetCookie;

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
                config("verisure.request_headers.generic_request"),
                "utf8=%E2%9C%93&authenticity_token=" . urlencode($this->session->csrf) . "&verisure_rsi_login%5Bnick%5D=" . config("verisure.username") . "&verisure_rsi_login%5Bpasswd%5D=" . config("verisure.password") . "&button="
            );
            $response = $this->client->send($loginRequest);
            log_response($response, $response->getBody()->getContents(), 'login');

            // Store the session cookie
            if ($cookie = $this->client->getConfig('cookies')->getCookieByName('_session_id')) {
                $this->storeSessionCookie($cookie);
                return $this;
            }
            throw new Exception("Session cookie was not returned after the login");
        }
        throw new Exception("Error during the login process");
    }

    /**
     * Get the Authenticity Token from the login page
     *
     * @return Session
     */
    protected function getAuthenticityToken(): Session
    {
        $request = new Request("GET", config("verisure.url") . "/gb/login/gb", config("verisure.request_headers.authenticity_token"), "");

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
        throw new Exception("Autenticity Token not found");
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
        // Delete all the active sessions
        Session::query()->delete();

        return "Sessions deleted";
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
     * Set the header for the guzzle client
     *
     * @return array
     */
    protected function headers(): array
    {
        return array_merge(
            config("verisure.request_headers.generic_request"),
            [
                "Cookie" => "accept_cookies=1; _session_id=" . $this->session->value,
                "X-Csrf-Token" => $this->session->csrf,
            ]
        );
    }

    /**
     * Activate the annex alarm
     *
     * @return string $job_id the Id of the current job
     */
    public function activateAnnex()
    {
        return $this->request('activate garage', 'panel/twice', '&typeAnnex=0&typeAnnex=1');
    }

    /**
     * Deactivate the annex alarm
     *
     * @return string $job_id the Id of the current job
     */
    public function deactivateAnnex()
    {
        return $this->request('deactivate garage', 'panel/twice', '&typeAnnex=0');
    }

    /**
     * Activate the main alarm
     *
     * @return string $job_id the Id of the current job
     */
    public function activate(string $mode)
    {
        return $this->request('activate house', 'panel/' . $mode);
    }

    /**
     * Deactivate the main alarm
     *
     * @return string $job_id the Id of the current job
     */
    public function deactivate()
    {
        return $this->request('deactivate house', 'panel/unlock');
    }

    /**
     * Check for the status of the alarm
     *
     * @return $this
     */
    public function status()
    {
        return $this->request('status request', 'panel/status');
    }

    /**
     * Make a Guzzle request to the specified endpoint
     *
     * @param string $endpoint
     * @param string $options optional body parameters
     * @return string $job_id the Id of the current job
     */
    protected function request(string $requestType, string $endpoint, string $options = ""): string
    {
        $this->setSession();
        $request = new Request(
            "POST",
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
        log_response($response, $body, $requestType);

        if ($response->getStatusCode() == 201) {
            return json_decode($body)->job_id;
        }
        throw new Exception("Server responded with status code: " . $response->getStatusCode());
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
            if ($counter > config('verisure.settings.status_job.max_calls')) {
                app('log')->error('problem requesting the job status: too many attempts');
                throw new Exception("Too many attempts");
            }

            $headers = $this->headers();
            unset($headers['Origin']);
            $request = new Request("GET", config("verisure.url") . "/es/remote/job_status/" . $jobId, $headers, "");

            $response = $this->client->send($request);
            $body = $response->getBody()->getContents();
            log_response($response, $body, 'job status');
            $response = json_decode($body);

            $status = $response->status;
            $counter++;
            // In production, add a timer between the requests
            if (!is_test() && $status !== "completed") {
                sleep(config('verisure.settings.status_job.sleep_between_calls'));
            }
        }

        if ($status == "failed") {
            // Note: the message in the failed response is not under ['message']['message']
            Record::create(['body' => $errorMessage = $response->message]);
            if ($errorMessage == "We have had problems identifying you, please end session and log in again."
                || $errorMessage == "Invalid session. Please, try again later.") {
                $this->logout();
            }
            return ["status" => $status, "message" => $response->message];
        }
        if ($status == "completed") {
            Record::create(['body' => $response->message->message]);
            return ["status" => $status, "message" => $response->message->message];
        }
        throw new Exception("Error in the response: " . $status);
    }
}
