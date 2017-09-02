<?php

namespace ChrisKonnertz\DeepLy;

use ChrisKonnertz\DeepLy\HttpClient\HttpClientInterface;
use ChrisKonnertz\DeepLy\HttpClient\CurlHttpClient;
use ChrisKonnertz\DeepLy\TranslationBag\TranslationBag;

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
     * The base URL of the API endpoint
     */
    const API_BASE_URL = 'https://www.deepl.com/jsonrpc/';

    /**
     * Current version number
     */
    const VERSION = '0.7';

    /**
     * @var HttpClientInterface
     */
    protected $httpClient = null;

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
        // Create a default HTTP client. You can call setHttpClient() to set another HTTP client.
        $this->httpClient = new CurlHttpClient();
    }

    /**
     * Translates a text. The $from argument is optional.
     *
     * @param string      $text The text you want to translate
     * @param string      $to   A self::LANG_<code> constant
     * @param string|null $from A self::LANG_<code> constant
     * @return string|null      Returns the translated text or null if there is no translation
     * @throws \Exception
     */
    public function translate($text, $to = self::LANG_EN, $from = self::LANG_AUTO)
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
            'jobs' => [
                [
                    'kind' => 'default',
                    'raw_en_sentence' => $text,
                ],
            ],
            'lang' => [
                'user_preferred_langs' => [
                    $from,
                    $to
                ],
                'source_lang_user_selected' => $from,
                'target_lang' => $to,
            ],
            'priority' => -1
        ];

        // The API call might throw an exception but we do not want to catch it,
        // the caller of this method should catch it instead.
        $rawResult = $this->httpClient->callApi(self::API_BASE_URL, $params);

        $translationBag = new TranslationBag($rawResult);

        $this->translationBag = $translationBag;

        return $translationBag->getBestTranslatedText();
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
     * Getter for the array with all supported language codes
     *
     * @param bool $withAuto
     * @return \string[]
     */
    public function getLangCodes($withAuto = true)
    {
        if ($withAuto) {
            return self::LANG_CODES;
        }

        // Attention! This only works as long as self::LANG_AUTO is the first item!
        return array_slice(self::LANG_CODES, 1);
    }

    /**
     * Getter for the TranslationBag object. Might return null!
     * The translation bag contains the raw result of the API call.
     *
     * @return TranslationBag|null
     */
    public function getTranslationBag()
    {
        return $this->translationBag;
    }

}
