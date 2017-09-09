<?php

namespace ChrisKonnertz\DeepLy\ResponseBag;

/**
 * This class handles the response content of a successful API split text call.
 * It checks its validity and offers methods to access the contents of the response content.
 * It implements an abstraction layer above the original response of the API call.
 */
class SentencesBag extends AbstractBag
{

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
        // Let the original method of the abstract base class do some basic checks
        parent::verifyResponseContent($responseContent);

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

}
