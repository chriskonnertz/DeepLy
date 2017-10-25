<?php

namespace ChrisKonnertz\DeepLy;

use ChrisKonnertz\DeepLy\HttpClient\CallException;
use ChrisKonnertz\DeepLy\HttpClient\HttpClientInterface;
use ChrisKonnertz\DeepLy\HttpClient\CurlHttpClient;
use ChrisKonnertz\DeepLy\Protocol\JsonRpcProtocol;
use ChrisKonnertz\DeepLy\Protocol\ProtocolInterface;
use ChrisKonnertz\DeepLy\ResponseBag\SentencesBag;
use ChrisKonnertz\DeepLy\ResponseBag\TranslationBag;

/**
 * This is the main class. Call its translate() method to translate text.
 */
class DeepLy
{

    /**
     * All supported language code constants
     * @see https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
     */
    const LANG_AUTO = 'auto'; // Let DeepL decide which language it is (only works for the source language)
    const LANG_DE = 'DE'; // German
    const LANG_EN = 'EN'; // English
    const LANG_FR = 'FR'; // French
    const LANG_ES = 'ES'; // Spanish
    const LANG_IT = 'IT'; // Italian
    const LANG_NL = 'NL'; // Dutch
    const LANG_PL = 'PL'; // Polish

    /**
     * Array with all supported language codes
     * @see https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
     */
    const LANG_CODES = [
        self::LANG_AUTO,
        self::LANG_DE,
        self::LANG_EN,
        self::LANG_FR,
        self::LANG_ES,
        self::LANG_IT,
        self::LANG_NL,
        self::LANG_PL,
    ];

    /**
     * Array with language codes as keys and the matching language names in English as values
     * @see https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
     */
    const LANG_NAMES = [
        self::LANG_AUTO => 'Auto',
        self::LANG_DE => 'German',
        self::LANG_EN => 'English',
        self::LANG_FR => 'French',
        self::LANG_ES => 'Spanish',
        self::LANG_IT => 'Italian',
        self::LANG_NL => 'Dutch',
        self::LANG_PL => 'Polish',
    ];

    /**
     * The length of the text for translations is limited by the API
     */
    const MAX_TRANSLATION_TEXT_LEN = 5000;

    /**
     * Constants that are names of methods that can be called via the API
     */
    const METHOD_TRANSLATE = 'LMT_handle_jobs'; // Translates a text
    const METHOD_SPLIT = 'LMT_split_into_sentences'; // Splits a text into sentences

    /**
     * The base URL of the API endpoint
     */
    const API_BASE_URL = 'https://www.deepl.com/jsonrpc/';

    /**
     * Current version number
     */
    const VERSION = '1.4.4';

    /**
     * If true, validate that the length of a translation text
     * is not greater than self::MAX_TRANSLATION_TEXT_LEN
     *
     * @var bool
     */
    protected $validateTextLength = true;

    /**
     * The protocol used for communication
     *
     * @var ProtocolInterface
     */
    protected $protocol;

    /**
     * The HTTP client used for communication
     *
     * @var HttpClientInterface
     */
    protected $httpClient;

    /**
     * This property stores the result (object) of the last translation
     *
     * @var TranslationBag|null
     */
    protected $translationBag = null;

    /**
     * DeepLy object constructor.
     */
    public function __construct()
    {
        // Create the default protocol object. You may call setProtocol() to switch it.
        $this->protocol = new JsonRpcProtocol();

        // Create the default HTTP client. You may call setHttpClient() to set another HTTP client.
        $this->httpClient = new CurlHttpClient($this->protocol);
    }

    /**
     * Uses the DeepL API to split a text into a string array of sentences.
     *
     * @param string $text The text you want to split into sentences
     * @param string $from Optional: A self::LANG_<code> constant
     * @return string[]
     * @throws \Exception
     */
    public function splitText($text, $from = self::LANG_AUTO)
    {
        if (! is_string($text)) {
            throw new \InvalidArgumentException('The $text argument has to be a string');
        }

        $params = [
            // We could add multiple items in the "texts" item, this would result in multiple items
            // in the "splitted_texts" array in the response
            'texts' => [
                $text
            ],
            'lang' => [
                'lang_user_selected' => $from
            ]
        ];

        $rawResponseData = $this->httpClient->callApi(self::API_BASE_URL, $params, self::METHOD_SPLIT);

        $responseContent = $this->protocol->processResponseData($rawResponseData);

        $splitTextBag = new SentencesBag($responseContent);

        $sentences = $splitTextBag->getAllSentences();

        return $sentences;
    }

    /**
     * Tries to detect the language of a text and returns its language code.
     * The language of the text has to be one of the supported languages or the result will be incorrect.
     * This method might throw an exception so you should wrap it in a try-catch-block.
     *
     * @param string      $text The text you want to analyze
     * @return string|null      Returns a language code from the self::LANG_CODES array or null
     * @throws \Exception
     */
    public function detectLanguage($text)
    {
        // Note: We always use English as the target language. if the source language is English as well,
        // DeepL automatically seems to set the target language to French so this is not a problem.
        $translationBag = $this->requestTranslation($text, self::LANG_EN, self::LANG_AUTO, true);

        return $translationBag->getSourceLanguage();
    }

    /**
     * Requests a translation from the API. Returns a TranslationBag object.
     * ATTENTION: The target language parameter is followed by the source language parameter!
     * This method might throw an exception so you should wrap it in a try-catch-block.
     *
     * @param string|string[] $text          The text to translate. A single string or an array of sentences (strings)
     * @param string          $to            Optional: A self::LANG_<code> constant
     * @param string|null     $from          Optional: A self::LANG_<code> constant
     * @param bool            $joinSentences If true, all sentences will be joined to one long sentence
     * @return TranslationBag
     * @throws \Exception
     */
    protected function requestTranslation($text, $to = self::LANG_EN, $from = self::LANG_AUTO, $joinSentences = false)
    {
        $this->translationBag = null;

        if (! is_string($text) and ! is_array($text)) {
            throw new \InvalidArgumentException('The $text argument has to be a string or an array');
        }
        if (is_array($text)) {
            foreach ($text as $index => $sentence) {
                if (! is_string($sentence)) {
                    throw new \InvalidArgumentException(
                        'The $text argument has to be a string or an array of strings. '.
                        'The '.(++$index).'. item of the array is not a string.'
                    );
                }
                // TODO Ensure that the limit is per sentence
                if ($this->validateTextLength and mb_strlen($sentence) > self::MAX_TRANSLATION_TEXT_LEN) {
                    throw new \InvalidArgumentException(
                        'The '.(++$index).'. sentence exceeds the maximum of '.self::MAX_TRANSLATION_TEXT_LEN.' chars'
                    );
                }
            }
        } else {
            if ($this->validateTextLength and mb_strlen($text) > self::MAX_TRANSLATION_TEXT_LEN) {
                throw new \InvalidArgumentException(
                    'The text exceeds the maximum of '.self::MAX_TRANSLATION_TEXT_LEN.' chars'
                );
            }
        }
        if (! is_string($to)) {
            throw new \InvalidArgumentException('The $to argument has to be a string');
        }
        if (! in_array($to, self::LANG_CODES)) {
            throw new \InvalidArgumentException('The $to argument has to be a valid language code');
        }
        if ($to === self::LANG_AUTO) {
            throw new \InvalidArgumentException('The $to argument cannot be "'.self::LANG_AUTO.'"');
        }
        if (! is_string($from)) {
            throw new \InvalidArgumentException('The $from argument has to be a string');
        }
        if (! in_array($from, self::LANG_CODES)) {
            throw new \InvalidArgumentException('The $from argument has to a valid language code');
        }

        // Note that this array will be converted to a data structure of arrays AND objects later on
        $params = [
            'lang' => [
                'source_lang_user_selected' => $from, // Attention: source_lang does not work!
                'target_lang' => $to,
            ]
        ];

        if ($joinSentences) {
            if (is_array($text)) {
                $text = implode(' ', $text);
            }

            // We could add multiple items in the "jobs" item, this would result in multiple items
            // in the "translations" array in the response
            $params['jobs'] = [
                [
                    'kind' => 'default',
                    'raw_en_sentence' => $text,
                ]
            ];
        } else {
            if (is_array($text)) {
                $sentences = $text;
            } else {
                // Let the API split the text into sentences
                $sentences = $this->splitText($text, $from);
            }

            $params['jobs'] = [];
            foreach ($sentences as $sentence) {
                $params['jobs'][] =  [
                    'kind' => 'default',
                    'raw_en_sentence' => $sentence,
                ];
            }
        }

        // The API call might throw an exception but we do not want to catch it,
        // the caller of this method should catch it instead.
        $rawResponseData = $this->httpClient->callApi(self::API_BASE_URL, $params, self::METHOD_TRANSLATE);

        $responseContent = $this->protocol->processResponseData($rawResponseData);

        $translationBag = new TranslationBag($responseContent);

        $this->translationBag = $translationBag;

        return $translationBag;
    }

    /**
     * Translates a text.
     * ATTENTION: The target language parameter is followed by the source language parameter!
     * This method might throw an exception so you should wrap it in a try-catch-block.
     *
     * @param string      $text          The text you want to translate
     * @param string      $to            Optional: A self::LANG_<code> constant
     * @param string|null $from          Optional: A self::LANG_<code> constant
     * @param bool        $joinSentences If true, all sentences will be joined to one long sentence
     * @return null|string               Returns the translated text or null if there is no translation
     */
    public function translate($text, $to = self::LANG_EN, $from = self::LANG_AUTO, $joinSentences = false)
    {
        $translationBag = $this->requestTranslation($text, $to, $from, $joinSentences);

        return $translationBag->getTranslation();
    }

    /**
     * Translates one text / sentence. Returns an array of translation proposals.
     * ATTENTION: The target language parameter is followed by the source language parameter!
     * This method might throw an exception so you should wrap it in a try-catch-block.
     *
     * @param string      $text The text you want to translate
     * @param string      $to   Optional: A self::LANG_<code> constant
     * @param string|null $from Optional: A self::LANG_<code> constant
     * @return string[]         Returns translation alternatives as a string array
     * @throws \Exception
     */
    public function proposeTranslations($text, $to = self::LANG_EN, $from = self::LANG_AUTO)
    {
        $translationBag = $this->requestTranslation($text, $to, $from, true);

        return $translationBag->getTranslationAlternatives();
    }

    /**
     * Translates a text. Returns a string array of translation sentences.
     * ATTENTION: The target language parameter is followed by the source language parameter!
     * This method might throw an exception so you should wrap it in a try-catch-block.
     *
     * @param string[]    $sentences The sentences you want to translate
     * @param string      $to        Optional: A self::LANG_<code> constant
     * @param string|null $from      Optional: A self::LANG_<code> constant
     * @param bool        $join      If true, join all sentences to a single string
     * @return \string[] Returns a string array (might be empty)
     */
    public function translateSentences(array $sentences, $to = self::LANG_EN, $from = self::LANG_AUTO, $join = false)
    {
        $translationBag = $this->requestTranslation($sentences, $to, $from);

        $translatedSentences = $translationBag->getTranslatedSentences();

        if ($join) {
            return implode(' ', $translatedSentences);
        }

        return $translatedSentences;
    }

    /**
     * Translates a text file. The $from argument is optional.
     * ATTENTION: The target language parameter is followed by the source language parameter!
     * This method will throw an exception if reading the file or translating fails
     * so you should wrap it in a try-catch-block.
     *
     * @param string      $filename      The name of the file you want to translate
     * @param string      $to            Optional: A self::LANG_<code> constant
     * @param string|null $from          Optional: A self::LANG_<code> constant
     * @param bool        $joinSentences If true, all sentences will be joined to one long sentence
     * @return string|null               Returns the translated text or null if there is no translation
     * @throws \Exception
     */
    public function translateFile($filename, $to = self::LANG_EN, $from = self::LANG_AUTO, $joinSentences = false)
    {
        if (! is_string($filename)) {
            throw new \InvalidArgumentException('The $filename argument has to be a string');
        }
        if (! is_readable($filename)) {
            throw new \InvalidArgumentException('Could not read file with the given filename');
        }

        $text = file_get_contents($filename);

        if ($text === false) {
            throw new \RuntimeException(
                'Could not read file with the given filename. Does this file exist and do we have read permission?'
            );
        }

        return $this->translate($text, $to, $from, $joinSentences);
    }

    /**
     * Pings the API server. Returns the duration in seconds until the response arrives
     * or throws an exception if no valid response was received.
     *
     * @return float
     * @throws CallException
     */
    public function ping()
    {
        return $this->httpClient->ping(self::API_BASE_URL);
    }

    /**
     * Setter for the validateTextLength property
     * If true, validate that the length of a translation text
     * is not greater than self::MAX_TRANSLATION_TEXT_LEN
     *
     * @return bool
     */
    public function getValidateTextLength()
    {
        return $this->validateTextLength;
    }

    /**
     * Getter for the validateTextLength property.
     * If true, validate that the length of a translation text
     * is not greater than self::MAX_TRANSLATION_TEXT_LEN
     *
     * @param bool $validateTextLength
     */
    public function setValidateTextLength($validateTextLength)
    {
        if (! is_bool($validateTextLength)) {
            throw new \InvalidArgumentException('$validateTextLength has to be boolean');
        }

        $this->validateTextLength = $validateTextLength;
    }

    /**
     * Getter for the protocol object
     *
     * @return ProtocolInterface
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * Setter for the protocol object
     *
     * @param ProtocolInterface $protocol
     */
    public function setProtocol(ProtocolInterface $protocol)
    {
        $this->protocol = $protocol;
    }

    /**
     * Getter for the HTTP client object
     *
     * @return HttpClientInterface
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * Setter for the HTTP client object. This allows you to use another HTTP client
     * than the default cURL based HTTP client.
     *
     * @param HttpClientInterface $httpClient
     */
    public function setHttpClient(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Decides if a language (code) is supported by DeepL(y).
     * Note that 'auto' is not a valid value in this context
     * except you explicitly set the $allowAuto param to true
     *
     * @param string $langCode  The language code, for example 'EN'
     * @param bool   $allowAuto Optional: If false, 'auto' is not a valid language
     * @return bool
     */
    public function supportsLangCode($langCode, $allowAuto = false)
    {
        if (! is_string($langCode)) {
            throw new \InvalidArgumentException('The $langCode argument has to be a string');
        }

        $supported = in_array($langCode, $this->getLangCodes($allowAuto));

        return $supported;
    }

    /**
     * Getter for the array with all supported language codes
     *
     * @param bool $withAuto Optional: If true, the 'auto' code will be in the returned array
     * @return \string[]
     */
    public function getLangCodes($withAuto = true)
    {
        if (! is_bool($withAuto)) {
            throw new \InvalidArgumentException('The $withAuto argument has to be boolean');
        }

        if ($withAuto) {
            return self::LANG_CODES;
        }

        // ATTENTION! This only works as long as self::LANG_AUTO is the first item!
        return array_slice(self::LANG_CODES, 1);
    }

    /**
     * Returns the English name of a language for a given language code.
     * The language code must be on of these: self::LANG_CODES
     *
     * @param string $langCode The code of the language
     * @return string
     */
    public function getLangName($langCode)
    {
        if (! in_array($langCode, self::LANG_CODES)) {
            throw new \InvalidArgumentException('The language code is unknown');
        }

        return self::LANG_NAMES[$langCode];
    }

    /**
     * Returns the language code of a language for a given language name.
     * The language name must be one of these: self::LANG_NAMES
     *
     * @param string $langName The name of the language
     * @return string
     */
    public function getLangCodeByName($langName)
    {
        if (! in_array($langName, self::LANG_NAMES)) {
            throw new \InvalidArgumentException('The language name is unknown');
        }

        return array_search($langName, self::LANG_NAMES);
    }

    /**
     * Getter for the TranslationBag object. Might return null!
     * The translation bag contains the result of the API call.
     *
     * @return TranslationBag|null
     */
    public function getTranslationBag()
    {
        return $this->translationBag;
    }

}
