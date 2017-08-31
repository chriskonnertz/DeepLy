<?php

namespace ChrisKonnertz\DeepLy\Connector;

/**
 * A class that implements the ConnectorInterface connects the Deeply library with the API server.
 */
interface ConnectorInterface
{

    /**
     * Makes the API call
     *
     * @param  string $url The URL of the API endpoint
     * @param  array  $params The data. Will be encoded as JSON
     * @return \stdClass Returns the decoded JSON result
     * @throws CallException
     */
    public function apiCall($url, array $params);

}