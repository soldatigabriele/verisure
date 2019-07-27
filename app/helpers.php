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
    function log_response(Response $response, $body)
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

        if ($request = Request::latest('id')->first()) {
            $request->response()->save($log);
        }
        $log->save();
    }
}
