<?php

namespace ChrisKonnertz\DeepLy\TranslationBag;

/**
 * This class handles the result of a successful API call.
 * It decodes the JSON result, checks its validity
 * and offers method to access the contents of the result.
 * It implements an abstraction layer above the original
 * result of the API call.
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
     * TranslationBag constructor.
     *
     * @param string $rawResult The raw result of an API call as string (usually contains stringified JSON)
     * @throws TranslationBagException
     */
    public function __construct($rawResult)
    {
        if (! is_string($rawResult)) {
            throw new \InvalidArgumentException('The $rawResult argument has to be a string');
        }

        $resultBag = json_decode($rawResult);

        $this->verifyResult($resultBag);

        // We only keep the inner result object, the other properties are no longer important
        $this->result = $resultBag->result;
    }

    /**
     * Verifies that the given result bag (usually a \stdClass built by json_decode)
     * is a valid result from an API call to the DeepL API.
     * This method will not return true/false but throw an exception if something is invalid.
     *
     * @param mixed|null $resultBag
     * @throws TranslationBagException
     * @return void
     */
    public function verifyResult($resultBag)
    {
        if (! $resultBag instanceof \stdClass) {
            throw new TranslationBagException('DeepLy API call did not return JSON that describes a \stdClass object');
        }

        if (property_exists($resultBag, 'error')) {
            if ($resultBag->error instanceof \stdClass and property_exists($resultBag->error, 'message')) {
                throw new TranslationBagException(
                    'DeepLy API call resulted in this error: '.$resultBag->error->message
                );
            } else {
                throw new TranslationBagException('DeepLy API call resulted in an unknown error');
            }
        }

        if (! property_exists($resultBag, 'result')) {
            throw new TranslationBagException(
                'DeepLy API call resulted in a malformed result - inner result is missing'
            );
        }
        if (! $resultBag->result instanceof \stdClass) {
            throw new TranslationBagException(
                'DeepLy API call resulted in a malformed result - inner result is not a \stdClass'
            );
        }

        if (! property_exists($resultBag->result, 'translations')) {
            throw new TranslationBagException(
                'DeepLy API call resulted in a malformed result - translations are missing'
            );
        }
        if (! is_array($resultBag->result->translations)) {
            throw new TranslationBagException(
                'DeepLy API call resulted in a malformed result - translations are not an array'
            );
        }

        if (sizeof($resultBag->result->translations) > 0) {
            foreach ($resultBag->result->translations as $index => $translation) {
                if (! property_exists($translation, 'beams')) {
                    throw new TranslationBagException(
                        'DeepLy API call resulted in a malformed result - beams are missing for translation '.$index
                    );
                }
                if (! is_array($translation->beams)) {
                    throw new TranslationBagException(
                        'DeepLy API call resulted in a malformed result - beams are not an array in translation '.$index
                    );
                }
            }
        }
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
     * Returns an array with all translations.
     * They are returned as an array of \stdClass objects.
     * These objects have a property called "postprocessed_sentence"
     * that contains the actual text of the translation.
     *
     * @return \stdClass[]
     * @throws TranslationBagException
     */
    public function getAllTranslations()
    {
        if (sizeof($this->result->translations) === 0) {
            return [];
        }

        // Unfortunately - without an API documentation - we do not exactly know the meaning of
        // "translations" and "beams". We assume that the style of our API call always will result
        // in exactly one item in the translations array.
        // Wording definition: When we speak of "translations" we actually mean beams.
        // For now we simply ignore the existence of the translations array in the result object.
        $translation = $this->result->translations[0];

        // Actually the beams array contains different translation proposals so we return it
        return $translation->beams;
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
