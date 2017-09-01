<?php

namespace ChrisKonnertz\DeepLy\Connector;

/**
 * This class handles the result of a successful API call.
 * It decodes the JSON result, checks its validity
 * and offers method to access the contents of the result.
 */
class TranslationBag
{

    /**
     * The (inner) result object from the API call
     *
     * @var \stdClass
     */
    protected $result;

    /**
     * CurlConnector constructor.
     *
     * @param string $rawResult The raw result of an API call as string (usually contains stringified JSON)
     * @throws ResultException
     */
    public function __construct($rawResult)
    {
        if (! is_string($rawResult)) {
            throw new \InvalidArgumentException('The $rawResult argument has to be a string');
        }

        $resultBag = json_decode($rawResult);

        if (! $resultBag instanceof \stdClass) {
            throw new ResultException('DeepLy API call did not return JSON that describes a \stdClass object');
        }

        if (property_exists($resultBag, 'error')) {
            if ($resultBag->error instanceof \stdClass and property_exists($resultBag->error, 'message')) {
                throw new ResultException('DeepLy API call resulted in this error: '.$resultBag->error->message);
            } else {
                throw new ResultException('DeepLy API call resulted in an unknown error');
            }
        }

        if (! property_exists($resultBag, 'result')) {
            throw new ResultException('DeepLy API call resulted in a malformed result - inner result is missing');
        }
        if (! $resultBag->result instanceof \stdClass) {
            throw new ResultException(
                'DeepLy API call resulted in a malformed result - inner result is not a \stdClass'
            );
        }

        if (! property_exists($resultBag->result, 'translations')) {
            throw new ResultException('DeepLy API call resulted in a malformed result - translations are missing');
        }
        if (! is_array($resultBag->result->translations)) {
            throw new ResultException('DeepLy API call resulted in a malformed result - translations are not an array');
        }

        // We only keep the inner result object, the other properties are no longer important
        $this->result = $resultBag->result;
    }

    /**
     * Returns a translation from the result of the API call.
     * Tries to return the "best" translation (which is the first).
     * Returns null if there is no translation.
     *
     * @return string|null
     */
    public function getBestTranslatedText()
    {
        // The result might contain multiple translations...
        $allTranslations = $this->getAllTranslations();

        // ...but we simply choose the first
        if (sizeof($allTranslations) > 0) {
            $firstTranslation = $allTranslations[0]->postprocessed_sentence;
        } else {
            $firstTranslation = null;
        }

        return $firstTranslation;
    }

    /**
     * Returns an array with all translations
     *
     * @return \stdClass[]
     */
    public function getAllTranslations()
    {
        if (sizeof($this->result->translations) === 0) {
            return [];
        }

        // TODO Also check if beams exists and its type?

        return $this->result->translations[0]->beams;
    }

    /**
     * Getter for the (inner) result object from the API call.
     *
     * @return \stdClass
     */
    public function getResult()
    {
        return $this->result;
    }

}
