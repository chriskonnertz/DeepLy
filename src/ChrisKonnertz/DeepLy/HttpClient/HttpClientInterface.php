<?php

namespace ChrisKonnertz\DeepLy\HttpClient;

/**
 * A class that implements the HttpClientInterface allows the DeepLy library
 * to communicate with the API server. Basically it is responsible for API
 * calls from the client to the API server. It does not have to do anything
 * with the result except returning it.
 */
interface HttpClientInterface
{

    /**
     * Executes an API call (a request) and returns the raw response data
     *
     * @param  string $url     The full URL of the API endpoint
     * @param  string $apiKey  The DeepL.com API key
     * @param  array  $payload The payload of the request. Will be encoded as JSON
     * @return string          The raw response data as string (usually contains stringified JSON)
     * @throws CallException  Throws a call exception if the call could not be executed
     */
    public function callApi(string $url, string $apiKey, array $payload = []) : string;

    /**
     * Pings the API server. Returns the duration in seconds
     * or throws an exception if no valid response was received.
     *
     * @param string $url
     * @return float
     * @throws CallException
     */
    public function ping(string $url);

}
