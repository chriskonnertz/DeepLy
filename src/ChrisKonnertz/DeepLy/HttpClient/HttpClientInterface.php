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
     * Request methods
     */
    const METHOD_POST = 'POST';
    const METHOD_GET = 'GET';
    const METHOD_DELETE = 'DELETE';

    /**
     * Maps HTTP error codes sent by the DeepL.com API to error messages according to
     * https://www.deepl.com/de/docs-api/accessing-the-api/error-handling/
     */
    public const ERRORS = [
        400 => 'Bad request. Please check error message and your parameters.',
        403 => 'Authorization failed. Please supply a valid auth_key parameter.',
        404 => 'The requested resource could not be found.',
        413 => 'The request size exceeds the limit.',
        414 => 'The request URL is too long. You can avoid this error by using a POST request instead of a GET request, and sending the parameters in the HTTP body.',
        429 => 'Too many requests. Please wait and resend your request.',
        456 => 'Quota exceeded. The character limit has been reached.',
        503 => 'Resource currently unavailable. Try again later.',
        529 => 'Too many requests. Please wait and resend your request.',
    ];

    /**
     * Executes a low level API call (a request) and returns the raw response data
     *
     * @param  string $url     The full URL of the API endpoint
     * @param  string $apiKey  The DeepL.com API key
     * @param  array  $payload The payload of the request. Will be encoded as JSON
     * @param  string $method  The request method ('GET', 'POST', 'DELETE')
     * @param  ?string $filename The filename of a file that should be uploaded
     * @return string          The raw response data as string (usually contains stringified JSON)
     * @throws CallException  Throws a call exception if the call could not be executed
     */
    public function callApi(
        string $url,
        string $apiKey,
        array $payload = [],
        string $method = self::METHOD_POST,
        string $filename = null
    ) : string;

    /**
     * Pings the API server. Returns the duration in seconds
     * or throws an exception if no valid response was received.
     *
     * @param string $url
     * @return float
     * @throws CallException
     */
    public function ping(string $url) : float;

}
