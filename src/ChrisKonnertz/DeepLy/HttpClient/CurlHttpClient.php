<?php

namespace ChrisKonnertz\DeepLy\HttpClient;

use ChrisKonnertz\DeepLy\Protocol\ProtocolInterface;

/**
 * This class uses cURL to execute API calls.
 */
class CurlHttpClient implements HttpClientInterface
{

    /**
     * Set this to false if you do not want cURL to
     * try to verify the SSL certificate - it could fail
     *
     * @see https://snippets.webaware.com.au/howto/stop-turning-off-curlopt_ssl_verifypeer-and-fix-your-php-config/
     *
     * @var bool
     */
    protected $sslVerifyPeer = true;

    /**
     * The protocol object that represents the protocol used for communication with the API
     *
     * @var ProtocolInterface
     */
    protected $protocol;

    /**
     * CurlHttpClient constructor.
     *
     * @param ProtocolInterface $protocol
     */
    public function __construct(ProtocolInterface $protocol)
    {
        if (! $this->isCurlAvailable()) {
            throw new \LogicException(
                'Cannot create instance of DeepLy\'s CurlHttpClient class, because the cURL PHP extension is not available'
            );
        }

        $this->protocol = $protocol;
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

        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $this->sslVerifyPeer);

        // Note: We assume that we always will use JSON to encode data
        // so this is independent from the protocol that we actually use
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)) // Note: We do not need mb_strlen here since JSON encodes Unicode
        );

        $rawResponseData = curl_exec($curl);

        if ($rawResponseData === false) {
            $exception = new CallException('cURL error during DeepLy API call: '.curl_error($curl));
            throw $exception;
        }

        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($code !== 200) {
            // Note that the response probably will contain an error description wrapped in a HTML page.
            // We extract the text and display add it tot he exception message.
            $text = $this->getTextFromHtml($rawResponseData);
            if ($text !== null) {
                $text = ', message: "'.$text.'"';
            }

            throw new CallException('Server side error during DeepLy API call: HTTP code '.$code.$text);
        }

        // TODO: Do not start a new session for each request?
        curl_close($curl);

        return $rawResponseData;
    }

    /**
     * Returns the text from an HTML document (passed as an HTML code string).
     * The text ist trimmed and line breaks are replaced by dashes.
     * Returns null if extracting the text was not possible.
     *
     * @param string $htmlCode
     * @return string|null
     */
    protected function getTextFromHtml($htmlCode)
    {
        $document = new \DOMDocument();

        $okay = $document->loadHTML($htmlCode);

        // Cannot load HTML document, not a valid HTML document
        if (!$okay) {
            return null;
        }

        $bodyElements = $document->getElementsByTagName('body');

        // Cannot find body-Element, not a valid HTML-Document
        if (sizeof($bodyElements) != 1) {
            return null;
        }

        /** @var \DOMElement $bodyElement */
        $bodyElement = $bodyElements[0];
        $text = $bodyElement->nodeValue;

        // Notes:
        // - It is not necessary to use some kind of "mb_trim()" function since trim() works with unicode chars
        // - We know that the server will add \n chars but no \r chars
        $text = str_replace("\n", ' - ', trim($text));

        return $text;
    }

    /**
     * Pings the API server. Returns the duration in seconds
     * or throws an exception if no valid response was received.
     *
     * @param string $url The URL of the API endpoint
     * @return float
     * @throws CallException
     */
    public function ping($url)
    {
        if (! is_string($url)) {
            throw new \InvalidArgumentException('$url has to be a string');
        }

        $curl = curl_init($url);

        // Do not "include the header in the output" (from the docs).
        // Should make the response a little bit smaller.
        curl_setopt($curl, CURLOPT_HEADER, 0);
        // Set this to true, because if it is set to false, curl will echo the result!
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $startedAt = microtime(true);
        $result = curl_exec($curl);
        $duration = microtime(true) - $startedAt;

        // TODO: Do not start a new session for each request?
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
    public function getSslVerifyPeer()
    {
        return $this->sslVerifyPeer;
    }

    /**
     * Setter for the sslVerifyPeer property
     *
     * @param bool $sslVerifyPeer
     */
    public function setSslVerifyPeer($sslVerifyPeer)
    {
        if (! is_bool($sslVerifyPeer)) {
            throw new \InvalidArgumentException('$sslVerifyPeer has to be boolean');
        }
        
        $this->sslVerifyPeer = $sslVerifyPeer;
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
     * Getter for the protocol object
     *
     * @return ProtocolInterface
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * Setter for the protocol object
     *
     * @param ProtocolInterface $protocol
     */
    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;
    }

}
