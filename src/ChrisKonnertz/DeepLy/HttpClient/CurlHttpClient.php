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
            throw new CallException('cURL error during DeepLy API call: '.curl_error($curl));
        }

        return $rawResponseData;
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
    protected function isCurlAvailable()
    {
        return (in_array('curl', get_loaded_extensions()));
    }

}
