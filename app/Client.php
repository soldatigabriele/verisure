<?php

namespace App;

use App\Request;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\RequestInterface;

class Client extends GuzzleClient
{
    /**
     * Extends the Guzzle send method and log the request
     *
     * @param RequestInterface $request
     * @param array $options
     * @return void
     */
    public function send(RequestInterface $request, array $options = [])
    {
        Request::create([
            'method' => $request->getMethod(),
            'uri' => $request->getUri()->__toString(),
            'body' => $request->getBody()->__toString(),
            'headers' => $request->getHeaders(),
        ]);
        return parent::send($request, $options);
    }
}
