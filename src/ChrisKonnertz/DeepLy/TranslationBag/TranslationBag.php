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
     * Verifies that the given response content (usually a \stdClass built by json_decode())
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
                    if (! property_exists($beam, 'postprocessed_sentence')) {
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
    public function getTranslation()
    {
        if (sizeof($this->responseContent->translations) === 0) {
            return null;
        }

        $translatedText = '';

        foreach ($this->responseContent->translations as $translation) {
            // The beams array contains 1-n translation alternatives.
            // The first one (index 0) is the "best" one (best score)
            $beam = $translation->beams[0];

            if ($translatedText !== '') {
                $translatedText .= ' ';
            }

            $translatedText .= $beam->postprocessed_sentence;
        }

        return $translatedText;
    }

    /**
     * Returns the translation alternatives for a single translation / sentence
     * as a string array. Might return an empty array.
     *
     * @return string[]
     */
    public function getTranslationAlternatives()
    {
        if (sizeof($this->responseContent->translations) === 0) {
            return [];
        }

        if (sizeof($this->responseContent->translations) > 1) {
            throw new \LogicException('This method does not operate on more than one source text');
        }

        $beams = $this->responseContent->translations[0]->beams;

        $translationAlternatives = array_column($beams, 'postprocessed_sentence');

        return $translationAlternatives;
    }

    /**
     * Returns a string array (might be empty) with all translation texts.
     * Every item in the result array will be one sentence.
     *
     * @return string[]
     */
    public function getTranslatedSentences()
    {
        $translatedTexts = [];

        foreach ($this->responseContent->translations as $translation) {
            // The beams array contains 1-n translation alternatives.
            // The first one (index 0) is the "best" one (best score)
            $translatedTexts = $translation->beams[0]->postprocessed_sentence;
        }

        return $translatedTexts;
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
