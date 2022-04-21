<?php

namespace ChrisKonnertz\DeepLy\HttpClient;

/**
 * This class uses cURL to execute API calls.
 */
class CurlHttpClient implements HttpClientInterface
{

    const LOGFILE_NAME = 'curl_log.txt';

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
     * If true, log cURL request to a logfile (self:: LOGFILE_NAME).
     * To get even more info about the actual request, send it to requestcatcher.com!
     *
     * @var bool
     */
    private bool $logging;

    /**
     * CurlHttpClient constructor.
     *
     * @param bool $logging If true, log cURL request to a logfile (self:: LOGFILE_NAME)
     */
    public function __construct(bool $logging = false)
    {
        if (! $this->isCurlAvailable()) {
            throw new \LogicException(
                'Cannot create instance of DeepLy\'s CurlHttpClient class, because the cURL PHP extension is not loaded'
            );
        }

        $this->logging = $logging;
    }

    /**
     * Executes a low level API call (a request) and returns the raw response data
     *
     * @param  string  $url      The full URL of the API endpoint
     * @param  string  $apiKey   The DeepL.com API key
     * @param  array   $payload  The payload of the request. Will be encoded as JSON
     * @param  string  $method   The request method ('GET', 'POST', 'DELETE')
     * @param  ?string $filename The filename of a file that should be uploaded
     * @return string            The raw response data as string (usually contains stringified JSON)
     * @throws CallException     Throws a call exception if the call could not be executed
     */
    public function callApi(
        string $url,
        string $apiKey,
        array $payload = [],
        string $method = HttpClientInterface::METHOD_POST,
        string $filename = null) : string
    {
        $curl = curl_init($url);

        $headers = ['Authorization: DeepL-Auth-Key '.$apiKey];

        if ($filename) {
            $payload['file'] = curl_file_create($filename);
            curl_setopt($curl, CURLOPT_POST, true);

            $headers[] = 'Content-Type: multipart/form-data';
        }

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $filename ? $payload : http_build_query($payload));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $this->sslVerifyPeer);

        // Set API key via header
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        // Log cURL request to a logfile
        if ($this->logging) {
            $logfile = fopen(__DIR__.'/'.self::LOGFILE_NAME, 'w');
            curl_setopt($curl, CURLOPT_VERBOSE, 1);
            curl_setopt($curl, CURLOPT_STDERR, $logfile);
        }

        $rawResponseData = curl_exec($curl);

        $this->handleError($curl, $rawResponseData);
        
        curl_close($curl);
        if ($this->logging) {
            fclose($logfile);
        }

        return $rawResponseData;
    }

    /**
     * Handle cURL errors / API errors (if there are any) by throwing an exception
     *
     * @param \CurlHandle $curl
     * @param bool|string $rawResponseData
     * @return void
     * @throws CallException
     */
    public function handleError(\CurlHandle $curl, bool|string $rawResponseData)
    {
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

    /**
     * Enable or disable logging to a logfile (self::LOGFILE_NAME)
     *
     * @param bool $enable
     * @return void
     */
    public function setLogging(bool $enable)
    {
        $this->logging = $enable;
    }

}
