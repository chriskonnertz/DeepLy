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
     * Especially it will throw an exception if the API was not able to auto-detected the language
     * (if no language code was given).
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
                'DeepLy API call resulted in a malformed result - splitted_texts property is missing', 100
            );
        }
        if (! is_array($responseContent->splitted_texts)) {
            throw new BagException(
                'DeepLy API call resulted in a malformed result - splitted_texts property is not an array', 101
            );
        }
        if (! property_exists($responseContent, 'lang')) {
            throw new BagException('DeepLy API call resulted in a malformed result - lang property is missing', 120);
        }
        if ($responseContent->lang === '') {
            throw new BagException('DeepL could not auto-detect the language of the text', 130);
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
     * Returns all sentences from the response, but the groups are kept,
     * so this method returns an array of array of strings.
     *
     * @return string[][]
     */
    public function getAllSentencesGrouped()
    {
        return $this->responseContent->splitted_texts;
    }

    /**
     * Returns the language code of the sentences. This is useful when the API auto-detected the language.
     *
     * @return string A DeepLy::LANG_<code> constant
     */
    public function getLanguage()
    {
        return $this->responseContent->lang;
    }

}
