<?php

namespace ChrisKonnertz\DeepLy\HttpClient;

/**
 * This class uses cURL to execute API calls.
 */
class CurlHttpClient implements HttpClientInterface
{

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
     * Set this to false if you do not want cURL to
     * try to verify the SSL certificate - it could fail
     *
     * @see https://snippets.webaware.com.au/howto/stop-turning-off-curlopt_ssl_verifypeer-and-fix-your-php-config/
     *
     * @var bool
     */
    protected bool $sslVerifyPeer = true;

    /**
     * CurlHttpClient constructor.
     */
    public function __construct()
    {
        if (! $this->isCurlAvailable()) {
            throw new \LogicException(
                'Cannot create instance of DeepLy\'s CurlHttpClient class, because the cURL PHP extension is not loaded'
            );
        }
    }

    /**
     * Executes an API call (a request) and returns the raw response data
     *
     * @param  string $url     The full URL of the API endpoint
     * @param  string $apiKey  The DeepL.com API key
     * @param  array  $payload The payload of the request. Will be encoded as JSON
     * @param  string $method  The request method ('GET', 'POST', 'DELETE')
     * @return string          The raw response data as string (usually contains stringified JSON)
     * @throws CallException   Throws a call exception if the call could not be executed
     */
    public function callApi(string $url, string $apiKey, array $payload = [], string $method = HttpClientInterface::METHOD_POST) : string
    {
        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($payload));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $this->sslVerifyPeer);

        // Set API key via header
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: DeepL-Auth-Key '.$apiKey
        ]);

        $rawResponseData = curl_exec($curl);

        // Check if cURL had any error
        if ($rawResponseData === false) {
            throw new CallException('cURL error during DeepLy API call: '.curl_error($curl));
        }

        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // Check if the API returned any error
        if ($code < 200 || $code >= 300) {
            $extraText = '';

            // Some errors make the API respond with an object that contains an error message
            if ($rawResponseData) {
                $decoded = json_decode($rawResponseData);
                if ($decoded) { // Note: $decoded will is null if the JSON cannot be decoded / if there is no valid JSON
                    $extraText .= ' Error message: "'.$decoded->message.'"';
                    if (isset($decoded->detail)) {
                        $extraText .= ' Error details: "'.$decoded->detail.'"';
                    }
                }
            }

            throw new CallException('Server side error during DeepLy API call: HTTP code '.$code
                .', description: "'.(self::ERRORS[$code] ?? 'Internal error').'"'.$extraText, $code);
        }
        
        curl_close($curl);

        return $rawResponseData;
    }

    /**
     * Pings the API server. Returns the duration in seconds
     * or throws an exception if no valid response was received.
     *
     * @param string $url The URL of the API endpoint
     * @return float
     * @throws CallException
     */
    public function ping(string $url) : float
    {
        $curl = curl_init($url);

        // Do not "include the header in the output" (from the docs).
        // Should make the response a little smaller.
        curl_setopt($curl, CURLOPT_HEADER, 0);
        // Set this to true, because if it is set to false, curl will echo the result!
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $startedAt = microtime(true);
        $result = curl_exec($curl);
        $duration = microtime(true) - $startedAt;

        curl_close($curl);

        if ($result === false) {
            throw new CallException('Did not get a valid response. API seems to be unreachable.');
        }

        return $duration;
    }

    /**
     * Getter for the sslVerifyPeer property
     *
     * @return bool
     */
    public function getSslVerifyPeer(): bool
    {
        return $this->sslVerifyPeer;
    }

    /**
     * Setter for the sslVerifyPeer property
     *
     * @param bool $sslVerifyPeer
     */
    public function setSslVerifyPeer(bool $sslVerifyPeer)
    {
        $this->sslVerifyPeer = $sslVerifyPeer;
    }

    /**
     * Returns true if the cURL extension is available, false otherwise
     *
     * @return bool
     */
    public function isCurlAvailable(): bool
    {
        return (in_array('curl', get_loaded_extensions()));
    }

}
