<?php

namespace ChrisKonnertz\DeepLy;

use ChrisKonnertz\DeepLy\Connector\ConnectorInterface;
use ChrisKonnertz\DeepLy\Connector\CurlConnector;

/**
 * This is the main class. Call its translate() method to translate text.
 */
class DeepLy
{

    /**
     * All available language code constants
     */
    const LANG_DE = 'DE'; // German
    const LANG_EN = 'EN'; // English
    const LANG_FR = 'FR'; // French
    const LANG_ES = 'ES'; // Spanish
    const LANG_IT = 'IT'; // Italian
    const LANG_NL = 'NL'; // Dutch
    const LANG_PL = 'PL'; // Polish

    /**
     * The base URL of the API endpoint
     */
    const API_BASE_URL = 'https://www.deepl.com/jsonrpc/';

    /**
     * Current version number
     */
    const VERSION = '0.1';

    /**
     * Array with all available language codes
     *
     * @var string[]
     */
    protected $langCodes = [self::LANG_EN, self::LANG_DE];

    /**
     * @var ConnectorInterface
     */
    protected $connector = null;

    /**
     * DeepLy object constructor.
     */
    public function __construct()
    {
        // Create a default connector. You can call setConnector() to set another connector.
        $this->connector = new CurlConnector();
    }

    /**
     * Translates a text. The $from argument is optional.
     *
     * @param string      $text The text you want to translate
     * @param string      $to   A self::LANG_<code> constant
     * @param string|null $from A self::LANG_<code> constant or null for auto detect
     * @return string           Returns the translated text
     * @throws \Exception
     */
    public function translate($text, $to = self::LANG_EN, $from = null)
    {
        if (! is_string($text)) {
            throw new \InvalidArgumentException('The $text argument has to be a string');
        }
        if (! is_string($to)) {
            throw new \InvalidArgumentException('The $to argument has to be a string');
        }
        if (! in_array($to, $this->langCodes)) {
            throw new \InvalidArgumentException('The $to argument has to be a valid language code');
        }
        if (! ($from === null or is_string($from))) {
            throw new \InvalidArgumentException('The $from argument has to be a string or null');
        }
        if (is_string($from) and ! in_array($from, $this->langCodes)) {
            throw new \InvalidArgumentException('The $from argument has to be null or a valid language code');
        }

        $connector = $this->connector;

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

        // The api call might throw an exception but we do not want to catch it,
        // the caller of this method should catch it instead.
        $resultBag = $connector->apiCall(self::API_BASE_URL, $params);

        // The result might contain multiple translations but we simply choose the first
        $translatedText = $resultBag->result->translations[0]->beams[0]->postprocessed_sentence;

        return $translatedText;
    }

    /**
     * @return ConnectorInterface
     */
    public function getConnector()
    {
        return $this->connector;
    }

    /**
     * @param ConnectorInterface $connector
     */
    public function setConnector(ConnectorInterface $connector)
    {
        $this->connector = $connector;
    }

}