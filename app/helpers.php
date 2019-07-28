<?php

use App\Request;
use GuzzleHttp\Psr7\Response;
use App\Response as LogResponse;

if (!function_exists('log_response')) {

    /**
     * Log the response from the server
     *
     * @param \GuzzleHttp\Psr7\Response $response
     * @return void
     */
    function log_response(Response $response, $body, string $type = "")
    {
        $log = new LogResponse;

        $body = json_decode($body, true);
        if (config('verisure.censure_responses')) {
            // Note: we remove the errors and the content we don't need as they
            // were returning a bunch of extra heavy data for every response
            $body['options']['user'] = '...';
            $body['name'] = '...';
        }

        // TODO this $response->getBody() gets unset in the logRespons ?? This is why we need to pass the response and body

        $log->status = $response->getStatusCode();
        $log->headers = $response->getHeaders();
        $log->request_type = $type;
        $log->body = $body;

        if ($request = Request::latest('id')->first()) {
            $request->response()->save($log);
        }
        $log->save();
    }

    /**
     * Check if the env is test
     *
     * @return bool
     */
    function is_test(): bool
    {
        return env("APP_ENV") == "testing";
    }
}
