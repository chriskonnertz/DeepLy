<?php

namespace ChrisKonnertz\DeepLy\HttpClient;

use ChrisKonnertz\DeepLy\Protocol\ProtocolInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Stream;

/**
 * This class uses cURL to execute API calls.
 */
class GuzzleHttpClient implements HttpClientInterface
{

    /**
     * The protocol object that represents the protocol used for communication with the API
     *
     * @var ProtocolInterface
     */
    protected $protocol;

    /**
     * The Guzzle instance
     *
     * @var Client
     */
    protected $guzzle;

    /**
     * GuzzleHttpClient constructor.
     *
     * @param ProtocolInterface $protocol
     */
    public function __construct(ProtocolInterface $protocol)
    {
        if (! $this->isCurlAvailable()) {
            throw new \LogicException(
                'Cannot create instance of Guzzle, because the cURL PHP extension is not available'
            );
        }

        if (! $this->isGuzzleAvailable()) {
            throw new \LogicException(
                'Cannot create instance of Guzzle, because it is not available. '.
                'It is not installed or the autoloading is not working'
            );
        }

        $this->protocol = $protocol;

        $this->guzzle = new Client();
    }

    /**
     * Executes an API call (a request) and returns the raw response data
     *
     * @param  string $url     The URL of the API endpoint
     * @param  array  $payload The payload of the request. Will be encoded as JSON
     * @param  string $method  The name of the method of the API call
     * @return string          The raw response data as string (usually contains stringified JSON)
     * @throws CallException   Throws a call exception if the call could not be executed
     */
    public function callApi($url, array $payload, $method)
    {
        if (! is_string($url)) {
            throw new \InvalidArgumentException('$url has to be a string');
        }
        if (! is_string($method)) {
            throw new \InvalidArgumentException('$method has to be a string');
        }

        $jsonData = $this->protocol->createRequestData($payload, $method);

        $options = ['body' => $jsonData];

        try {
            $guzzleResponse = $this->guzzle->post($url, $options);
        } catch (GuzzleException $exception) {
            $callException = new CallException('cURL error during DeepLy API call: '.$exception->getMessage());
            throw $callException;
        }

        $code = $guzzleResponse->getStatusCode();
        if ($code !== 200) {
            // Note that the response probably will contain an error description wrapped in a HTML page
            throw new CallException('Server side error during DeepLy API call: HTTP code '.$code);
        }

        if ($guzzleResponse->getBody() instanceof Stream) {
            $rawResponseData = $guzzleResponse->getBody()->getContents();
        } else {
            // This should never happen
            throw new CallException('$guzzleResponse->getBody() did not return a Stream object');
        }

        return $rawResponseData;
    }

    /**
     * Pings the API server. Returns the duration in seconds
     * or throws an exception if no valid response was received.
     *
     * @param string $url
     * @return float
     * @throws CallException
     */
    public function ping($url)
    {
        if (! is_string($url)) {
            throw new \InvalidArgumentException('$url has to be a string');
        }

        try {
            $startedAt = microtime(true);
            $this->guzzle->get($url.'dsdasdasd');
            $duration = microtime(true) - $startedAt;
        } catch (GuzzleException $exception) {
            throw new CallException('Did not get a valid response. API seems to be unreachable.');
        }

        return $duration;
    }

    /**
     * Returns true if the cURL extension is available, false otherwise
     *
     * @return bool
     */
    public function isCurlAvailable()
    {
        return (in_array('curl', get_loaded_extensions()));
    }

    /**
     * Returns true if the Guzzle client is available via auto-loading
     *
     * @return bool
     */
    public function isGuzzleAvailable()
    {
        return class_exists(Client::class);
    }

}
