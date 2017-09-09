<?php

namespace ChrisKonnertz\DeepLy\ResponseBag;

/**
 * This class handles the response content of a successful API split text call.
 * It checks its validity and offers methods to access the contents of the response content.
 * It implements an abstraction layer above the original response of the API call.
 */
class SentencesBag
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

        if (! property_exists($responseContent, 'splitted_texts')) {
            throw new BagException(
                'DeepLy API call resulted in a malformed result - splitted_texts property is missing'
            );
        }
        if (! is_array($responseContent->splitted_texts)) {
            throw new BagException(
                'DeepLy API call resulted in a malformed result - splitted_texts property is not an array'
            );
        }
    }

    /**
     * Returns a string array with all sentences from the response.
     *
     * @return string[]
     */
    public function getAllSentences()
    {
        $splitTexts = $this->responseContent->splitted_texts;

        $sentences = [];

        foreach ($splitTexts as $splitText) {
            foreach ($splitText as $sentence) {
                $sentences[] = $sentence;
            }
        }

        return $sentences;
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
