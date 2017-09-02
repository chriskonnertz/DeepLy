<?php

namespace ChrisKonnertz\DeepLy\TranslationBag;

/**
 * This class handles the response content of a successful API translation call.
 * It checks its validity and offers method to access the contents of the response content.
 * It implements an abstraction layer above the original response of the API call.
 */
class TranslationBag
{

    /**
     * The response content (payload) object of a translation API call
     *
     * @var \stdClass
     */
    protected $responseContent;

    /**
     * TranslationBag constructor.
     *
     * @param \stdClass $responseContent The response content (payload) of a translation API call
     * @throws TranslationBagException
     */
    public function __construct(\stdClass $responseContent)
    {
        $this->verifyResponseContent($responseContent);

        $this->responseContent = $responseContent;
    }

    /**
     * Verifies that the given response content (usually a \stdClass built by json_decode)
     * is a valid result from an API call to the DeepL API.
     * This method will not return true/false but throw an exception if something is invalid.
     *
     * @param mixed|null $responseContent The response content (payload) of a translation API call
     * @throws TranslationBagException
     * @return void
     */
    public function verifyResponseContent($responseContent)
    {
        if (! $responseContent instanceof \stdClass) {
            throw new TranslationBagException('DeepLy API call did not return JSON that describes a \stdClass object');
        }

        if (property_exists($responseContent, 'error')) {
            if ($responseContent->error instanceof \stdClass and property_exists($responseContent->error, 'message')) {
                throw new TranslationBagException(
                    'DeepLy API call resulted in this error: '.$responseContent->error->message
                );
            } else {
                throw new TranslationBagException('DeepLy API call resulted in an unknown error');
            }
        }

        if (! property_exists($responseContent, 'translations')) {
            throw new TranslationBagException(
                'DeepLy API call resulted in a malformed result - translations are missing'
            );
        }
        if (! is_array($responseContent->translations)) {
            throw new TranslationBagException(
                'DeepLy API call resulted in a malformed result - translations are not an array'
            );
        }

        if (sizeof($responseContent->translations) > 0) {
            foreach ($responseContent->translations as $index => $translation) {
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

                foreach ($translation->beams as $beamIndex => $beam) {
                    if (! property_exists($translation, 'postprocessed_sentence')) {
                        throw new TranslationBagException(
                            'DeepLy API call resulted in a malformed result - '.
                            'postprocessed_sentence property is missing in beam '.$index
                        );
                    }
                }
            }
        }
    }

    /**
     * Returns a translation from the response content of the API call.
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
        if (sizeof($this->responseContent->translations) === 0) {
            return [];
        }

        // Unfortunately - without an API documentation - we do not exactly know the meaning of
        // "translations" and "beams". We assume that the style of our API call always will result
        // in exactly one item in the translations array.
        // Wording definition: When we speak of "translations" we actually mean beams.
        // For now we simply ignore the existence of the translations array in the result object.
        $set = $this->responseContent->translations[0];

        // Actually the beams array contains different translation proposals so we return it
        return $set->beams;
    }

    /**
     * Returns an array (might be empty) with all translation texts.
     * The first result (index 0) is the "best" translation (highest score),
     * the others are alternatives.
     *
     * @return string[]
     */
    public function getTranslations()
    {
        if (sizeof($this->responseContent->translations) === 0) {
            return [];
        }

        // Unfortunately - without an API documentation - we do not exactly know the meaning of
        // "translations" and "beams". We assume that the style of our API call always will result
        // in exactly one item in the translations array.
        // Wording definition: When we speak of "translations" we actually mean beams.
        // For now we simply ignore the existence of the translations array in the result object.
        $set = $this->responseContent->translations[0];

        $beams = $set->beams;

        // Not sure if sorting is necessary - but better save than sorry
        usort($beams, function ($beamA, $beamB)
        {
            return ($beamA->score < $beamB->score) ? 1 : -1;
        });

        $translations = array_column($beams, 'postprocessed_sentence');

        // array_column() might return null instead of an empty array so create one
        if ($translations === null) {
            $translations = [];
        }

        return $translations;
    }

    /**
     * Getter for the response content (payload) object of a translation API call
     *
     * @return \stdClass
     */
    public function getResponseContent()
    {
        return $this->responseContent;
    }

}
