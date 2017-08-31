<?php

namespace ChrisKonnertz\DeepLy\Connector;

/**
 * This class uses cURL to execute API calls.
 */
class CurlConnector implements ConnectorInterface
{

    /**
     * Makes the API call
     *
     * @param  string $url The URL of the API endpoint
     * @param  array  $params The data. Will be encoded as JSON
     * @return \stdClass Returns the decoded JSON result
     * @throws CallException
     */
    public function apiCall($url, array $params)
    {
        $curl = curl_init($url);

        $data = [
            'jsonrpc' => '2.0', // Set the protocol version. @see https://en.wikipedia.org/wiki/JSON-RPC
            'method' => 'LMT_handle_jobs', // Set the method of the API call
            'params' => $params // Set the parameters
        ];

        $jsonData = json_encode($data);

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // TODO Do not try to verify the SSL certificate - it could fail
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData)) // TODO use mb strlen?
        );

        $result = curl_exec($curl);

        if ($result === false) {
            throw new CallException('cURL error in DeepLy API client: '.curl_error($curl));
        }

        $result = json_decode($result);

        return $result;
    }

}