<?php

namespace ChrisKonnertz\DeepLy\ResponseBag;

/**
 * This class is the abstract class for all bag classes.
 * A bag class handles the response content of a successful API call.
 * It checks its validity and offers methods to access the contents of the response content.
 * It implements an abstraction layer above the original response of the API call.
 */
abstract class AbstractBag
{

    /**
     * The response content (payload) object of a split text API call
     *
     * @var \stdClass
     */
    protected $responseContent;

    /**
     * SentencesBag constructor.
     *
     * @param \stdClass $responseContent The response content (payload) of a split text API call
     * @throws BagException
     */
    public function __construct(\stdClass $responseContent)
    {
        $this->verifyResponseContent($responseContent);

        $this->responseContent = $responseContent;
    }

    /**
     * Verifies that the given response content (usually a \stdClass built by json_decode())
     * is a valid result from an API call to the DeepL API.
     * This method will not return true/false but throw an exception if something is invalid.
     *
     * @param mixed|null $responseContent The response content (payload) of a split text API call
     * @throws BagException
     * @return void
     */
    public function verifyResponseContent($responseContent)
    {
        if (! $responseContent instanceof \stdClass) {
            throw new BagException('DeepLy API call did not return JSON that describes a \stdClass object');
        }

        if (property_exists($responseContent, 'error')) {
            if ($responseContent->error instanceof \stdClass and property_exists($responseContent->error, 'message')) {
                throw new BagException(
                    'DeepLy API call resulted in this error: '.$responseContent->error->message
                );
            } else {
                throw new BagException('DeepLy API call resulted in an unknown error');
            }
        }
    }

    /**
     * Getter for the response content (payload) object of a split text API call
     *
     * @return \stdClass
     */
    public function getResponseContent()
    {
        return $this->responseContent;
    }

}
