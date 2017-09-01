<?php

namespace ChrisKonnertz\DeepLy\Connector;

/**
 * A class that implements the ConnectorInterface connects the DeepLy library with the API server.
 */
interface ConnectorInterface
{

    /**
     * Executes an API call
     *
     * @param  string $url The URL of the API endpoint
     * @param  array  $params The data. Will be encoded as JSON
     * @return string The raw result as string (usually contains stringified JSON)
     * @throws CallException Throws a call exception if the call could not be executed
     */
    public function apiCall($url, array $params);

}
