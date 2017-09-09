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
     * Constants that are names of methods that can be called with the API
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
    const VERSION = '1.1.2';

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
        $this->protocol = new JsonRpcProtocol();

        // Create a default HTTP client. You can call setHttpClient() to set another HTTP client.
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
     * Requests a translation from the API. Returns a TranslationBag object.
     * ATTENTION: The target language parameter is followed by the source language parameter!
     * This method might throw an exception so you should wrap it in a try-catch-block.
     *
     * @param string      $text The text you want to translate
     * @param string      $to   Optional: A self::LANG_<code> constant
     * @param string|null $from Optional: A self::LANG_<code> constant
     * @param bool        $joinSentences If true, all sentences will be joined to one long sentence
     * @return TranslationBag
     * @throws \Exception
     */
    protected function requestTranslation($text, $to = self::LANG_EN, $from = self::LANG_AUTO, $joinSentences = false)
    {
        $this->translationBag = null;

        if (! is_string($text)) {
            throw new \InvalidArgumentException('The $text argument has to be a string');
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
                'source_lang' => $from,
                'target_lang' => $to,
            ]
        ];

        if ($joinSentences) {
            // We could add multiple items in the "jobs" item, this would result in multiple items
            // in the "translations" array in the response
            $params['jobs'] = [
                [
                    'kind' => 'default',
                    'raw_en_sentence' => $text,
                ]
            ];
        } else {
            $sentences = $this->splitText($text, $from);

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
     * @param string      $text The text you want to translate
     * @param string      $to   Optional: A self::LANG_<code> constant
     * @param string|null $from Optional: A self::LANG_<code> constant
     * @return string|null      Returns the translated text or null if there is no translation
     * @throws \Exception
     */
    public function translateSentences($text, $to = self::LANG_EN, $from = self::LANG_AUTO)
    {
        $translationBag = $this->requestTranslation($text, $to, $from);

        return $translationBag->getTranslatedSentences();
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
                'Could not read file with the given filename. Does this file exists and do we have read permission?'
            );
        }

        return $this->translate($text, $to, $from, $joinSentences);
    }

    /**
     * Pings the API server. Returns the duration in seconds
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
