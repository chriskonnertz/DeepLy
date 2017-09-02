<?php

namespace ChrisKonnertz\DeepLy\Protocol;

/**
 * JSON RPC is a remote procedure call protocol that uses JSOn to encode data.
 * This class represents this protocol.
 *
 * @see https://en.wikipedia.org/wiki/JSON-RPC
 */
class JsonRpcProtocol implements ProtocolInterface
{

    /**
     * Creates a request bag according to the JSON RPC protocol.
     * The API will be able to understand it.
     * The result is encoded as a JSON string.
     *
     * @param array        $payload The payload / parameters of the request. Will be encoded as JSON
     * @param null|string  $method  The method of the API call. Null = default
     * @return string
     */
    public function createRequestData(array $payload, $method = null)
    {
        if (is_null($method)) {
            $method = 'LMT_handle_jobs';
        }
        if (! is_string($method)) {
            throw new \InvalidArgumentException('The $method argument has to be null or of type string');
        }

        $data = [
            'jsonrpc' => '2.0', // Set the protocol version
            'method' => $method, // Set the method of the JSON RPC API call
            'params' => $payload // Set the parameters / the payload
        ];

        $jsonData = json_encode($data);

        return $jsonData;
    }

}