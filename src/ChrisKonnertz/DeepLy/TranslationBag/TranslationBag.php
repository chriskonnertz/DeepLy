<?php

namespace ChrisKonnertz\DeepLy\TranslationBag;

/**
 * This class handles the response data of a successful API translation call.
 * It checks its validity and offers method to access the contents of the response data.
 * It implements an abstraction layer above the original response data of the API call.
 */
class TranslationBag
{

    /**
     * The response data (payload) object of a translation API call
     *
     * @var \stdClass
     */
    protected $responseData;

    /**
     * TranslationBag constructor.
     *
     * @param \stdClass $responseData The response data (payload) of a translation API call
     * @throws TranslationBagException
     */
    public function __construct(\stdClass $responseData)
    {
        $this->verifyResponseData($responseData);

        $this->responseData = $responseData;
    }

    /**
     * Verifies that the given response data (usually a \stdClass built by json_decode)
     * is a valid result from an API call to the DeepL API.
     * This method will not return true/false but throw an exception if something is invalid.
     *
     * @param mixed|null $responseData The response data (payload) of a translation API call
     * @throws TranslationBagException
     * @return void
     */
    public function verifyResponseData($responseData)
    {
        if (! $responseData instanceof \stdClass) {
            throw new TranslationBagException('DeepLy API call did not return JSON that describes a \stdClass object');
        }

        if (property_exists($responseData, 'error')) {
            if ($responseData->error instanceof \stdClass and property_exists($responseData->error, 'message')) {
                throw new TranslationBagException(
                    'DeepLy API call resulted in this error: '.$responseData->error->message
                );
            } else {
                throw new TranslationBagException('DeepLy API call resulted in an unknown error');
            }
        }

        if (! property_exists($responseData, 'translations')) {
            throw new TranslationBagException(
                'DeepLy API call resulted in a malformed result - translations are missing'
            );
        }
        if (! is_array($responseData->translations)) {
            throw new TranslationBagException(
                'DeepLy API call resulted in a malformed result - translations are not an array'
            );
        }

        if (sizeof($responseData->translations) > 0) {
            foreach ($responseData->translations as $index => $translation) {
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
     * Returns a translation from the response data of the API call.
     * Tries to return the "best" translation (which is the first).
     * Returns null if there is no translation.
     *
     * @return string|null
     */
    public function getBestTranslatedText()
    {
        // The result might contain multiple translations...
        $rawTranslations = $this->getRawTranslations();

        // ...but we simply choose the first
        if (sizeof($rawTranslations) > 0) {
            $firstTranslation = $rawTranslations[0]->postprocessed_sentence;
        } else {
            $firstTranslation = null;
        }

        return $firstTranslation;
    }

    /**
     * Returns an array with all raw translation objects.
     * They are returned as an array of \stdClass objects.
     * These objects have a property called "postprocessed_sentence"
     * that contains the actual text of the translation.
     *
     * @return \stdClass[]
     * @throws TranslationBagException
     */
    public function getRawTranslations()
    {
        if (sizeof($this->responseData->translations) === 0) {
            return [];
        }

        // Unfortunately - without an API documentation - we do not exactly know the meaning of
        // "translations" and "beams". We assume that the style of our API call always will result
        // in exactly one item in the translations array.
        // Wording definition: When we speak of "translations" we actually mean beams.
        // For now we simply ignore the existence of the translations array in the result object.
        $set = $this->responseData->translations[0];

        // Actually the beams array contains different translation proposals so we return it
        return $set->beams;
    }

    /**
     * Getter for the response data (payload) object of a translation API call
     *
     * @return \stdClass
     */
    public function getResponseData()
    {
        return $this->responseData;
    }

}
