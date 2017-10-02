<?php

namespace ChrisKonnertz\DeepLy\ResponseBag;

/**
 * This class handles the response content of a successful API translation call.
 * It checks its validity and offers methods to access the contents of the response content.
 * It implements an abstraction layer above the original response of the API call.
 */
class TranslationBag extends AbstractBag
{

    /**
     * Verifies that the given response content (usually a \stdClass built by json_decode())
     * is a valid result from an API call to the DeepL API.
     * This method will not return true/false but throw an exception if something is invalid.
     *
     * @param mixed|null $responseContent The response content (payload) of a translation API call
     * @throws BagException
     * @return void
     */
    public function verifyResponseContent($responseContent)
    {
        // Let the original method of the abstract base class do some basic checks
        parent::verifyResponseContent($responseContent);

        if (! property_exists($responseContent, 'source_lang')) {
            throw new BagException('DeepLy API call resulted in a malformed result - source_lang attribute is missing');
        }
        if (! property_exists($responseContent, 'target_lang')) {
            throw new BagException('DeepLy API call resulted in a malformed result - target_lang attribute is missing');
        }

        if (! property_exists($responseContent, 'translations')) {
            throw new BagException(
                'DeepLy API call resulted in a malformed result - translations are missing'
            );
        }
        if (! is_array($responseContent->translations)) {
            throw new BagException(
                'DeepLy API call resulted in a malformed result - translations are not an array'
            );
        }

        if (sizeof($responseContent->translations) > 0) {
            foreach ($responseContent->translations as $index => $translation) {
                if (! property_exists($translation, 'beams')) {
                    throw new BagException(
                        'DeepLy API call resulted in a malformed result - beams are missing for translation '.$index
                    );
                }
                if (! is_array($translation->beams)) {
                    throw new BagException(
                        'DeepLy API call resulted in a malformed result - beams are not an array in translation '.$index
                    );
                }
                if (sizeof($translation->beams) == 0) {
                    throw new BagException(
                        'DeepLy API call resulted in a malformed result - beams array is empty in translation '.$index
                    );
                }

                foreach ($translation->beams as $beamIndex => $beam) {
                    if (! property_exists($beam, 'postprocessed_sentence')) {
                        throw new BagException(
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

        $translationAlternatives = [];
        foreach ($beams as $beam) {
            $translationAlternatives[] = $beam->postprocessed_sentence;
        }

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
            $translatedTexts[] = $translation->beams[0]->postprocessed_sentence;
        }

        return $translatedTexts;
    }

    /**
     * Returns the language code of the source ("from") language. Might have been auto-detected by DeepL.
     * Attention: DeepLy does not check if the result is in the Deeply::LANG_CODES array.
     * Therefore DeepLy also will work if DeepL adds support for new languages.
     *
     * @return string The language code, one of these: Deeply::LANG_CODES
     */
    public function getSourceLanguage()
    {
        return $this->responseContent->source_lang;
    }

    /**
     * Returns the language code of the target ("to") language.
     *
     * @return string The language code, one of these: Deeply::LANG_CODES
     */
    public function getTargetLanguage()
    {
        return $this->responseContent->target_lang;
    }

}
