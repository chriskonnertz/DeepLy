<?php

namespace ChrisKonnertz\DeepLy\Protocol;

/**
 * JSON RPC is a remote procedure call protocol that uses JSON to encode data.
 * This class represents this protocol.
 *
 * Note: This class does not support batch requests/responses
 *
 * @see https://en.wikipedia.org/wiki/JSON-RPC (Wikipedia article)
 * @see http://www.jsonrpc.org/specification (Official specification)
 */
class JsonRpcProtocol implements ProtocolInterface
{

    /**
     * The number of the supported protocol version
     */
    const PROTOCOL_VERSION = '2.0';

    /**
     * Validate the ID of the response?
     * JSON RPC supports to add an ID to each request/response
     * that can be used to identify a request/response pair.
     *
     * @var bool
     */
    protected $validateId = true;

    /**
     * ID of the last JSON RPC request, int >= 0 (> 0 if there was a request)
     * We use a static var here so multiple instances of this class should not interfere.
     *
     * @var int
     */
    protected static $lastId = 0;

    /**
     * Creates a request bag according to the JSON RPC protocol.
     * The API will be able to understand it.
     * The result is encoded as a JSON string.
     *
     * @param array  $payload The payload / parameters of the request. Will be encoded as JSON
     * @param string $method  The method of the API call.
     * @return string
     */
    public function createRequestData(array $payload, $method)
    {
        if (! is_string($method)) {
            throw new \InvalidArgumentException('The $method argument has to be null or of type string');
        }

        // Every JSON RPC request has a unique ID so that the request and its response can be linked.
        // WARNING: There is no absolute guarantee that this ID is unique!
        // Use this class like a singleton to ensure uniqueness.
        // Note: According to the specs of the JSON RPC protocol we can send an int or a string,
        // so uniqid() - which returns a string - should work. Unfortunately it does not.
        self::$lastId = ++self::$lastId;
        $id = self::$lastId;

        $data = [
            'jsonrpc' => self::PROTOCOL_VERSION, // Set the protocol version
            'method'  => $method, // Set the method of the JSON RPC API call
            'params'  => $payload, // Set the parameters / the payload
            'id'      => $id, // Set the ID of this request. (Omitting it would mean we do not expect a response)
        ];

        $jsonData = json_encode($data);

        return $jsonData;
    }

    /**
     * Processes the data from an response from the server to an API call.
     * Returns the payload (data) of the response or throws a ProtocolException.
     *
     * @param string $rawResponseData The data (payload) of the response as a stringified JSON string
     * @return \stdClass              The data (payload) of the response as an object structure
     * @throws ProtocolException|\InvalidArgumentException
     */
    public function processResponseData($rawResponseData)
    {
        if (! is_string($rawResponseData)) {
            throw new \InvalidArgumentException('The $rawResponseData argument has to be a string');
        }

        $responseData = json_decode($rawResponseData);

        $this->validateResponseData($responseData);

        // We only return the inner result (=content) object, the other properties are no longer important
        return $responseData->result;
    }

    /**
     * Validates that the response data (usually a \stdClass object built by json_decode)
     * is valid response data from an API call to the DeepL API using the JSON RPC protocol.
     *
     * @param mixed $responseData The response data, usually a \stdClass object
     * @throws ProtocolException
     */
    protected function validateResponseData($responseData)
    {
        if (! $responseData instanceof \stdClass) {
            throw new ProtocolException('DeepLy API call did not return JSON that describes a \stdClass object');
        }

        if (! property_exists($responseData, 'jsonrpc')) {
            throw new ProtocolException('
                The given response data does not seem to be come from a JSON RPC request - it has no "jsonrpc" property'
            );
        }
        if ($responseData->jsonrpc !== self::PROTOCOL_VERSION) {
            throw new ProtocolException(
                'The version of the JSON RPC response does not match the expected version '.self::PROTOCOL_VERSION
            );
        }

        if (property_exists($responseData, 'error')) {
            if ($responseData->error instanceof \stdClass and property_exists($responseData->error, 'message')) {
                $message = 'DeepLy API call resulted in this error: '.$responseData->error->message;

                // Note: According to the specs the error object can include a data property,
                // but the DeepL API (usually) does not seem to add it. We will completely ignore it.
                if (property_exists($responseData->error, 'code') and is_int($responseData->error->code)) {
                    // Note: The meanings of the codes are defined in the protocol specification
                    throw new ProtocolException($message, $responseData->error->code);
                } else {
                    throw new ProtocolException($message);
                }
            } else {
                throw new ProtocolException('DeepLy API call resulted in an unknown error');
            }
        }

        if ($this->validateId) {
            if (! property_exists($responseData, 'id')) {
                throw new ProtocolException('DeepLy API call resulted in a malformed result - ID property is missing');
            }
            if ($responseData->id !== self::$lastId) {
                throw new ProtocolException('DeepLy API call resulted in an invalid result - ID is not '.self::$lastId);
            }
        }

        if (! property_exists($responseData, 'result')) {
            throw new ProtocolException(
                'DeepLy API call resulted in a malformed result - inner result property is missing'
            );
        }
        if (! $responseData->result instanceof \stdClass) {
            // If we try to translate an empty string the API responses with a malformed generic error response.
            // Due to the nature of the response we do not know for sure what has caused it.
            throw new ProtocolException(
                'DeepLy API call resulted in a malformed result - inner result property is not a \stdClass. '.
                'A possible cause is that the API could not translate the text, for example if it is an empty string'
            );
        }
    }

    /**
     * Validate the ID of the response?
     *
     * @return bool
     */
    public function getValidateId()
    {
        return $this->validateId;
    }

    /**
     * Setter for the validateId property
     *
     * @param bool $validateId
     */
    public function setValidateId($validateId)
    {
        if (! is_bool($validateId)) {
            throw new \InvalidArgumentException('The $validateId argument must be a boolean');
        }

        $this->validateId = $validateId;
    }

}
