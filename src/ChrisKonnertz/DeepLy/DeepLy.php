<?php

namespace ChrisKonnertz\DeepLy;

use ChrisKonnertz\DeepLy\HttpClient\CallException;
use ChrisKonnertz\DeepLy\HttpClient\HttpClientInterface;
use ChrisKonnertz\DeepLy\HttpClient\CurlHttpClient;

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
    const LANG_BG = 'BG'; // Bulgarian
    const LANG_CS = 'CS'; // Czech
    const LANG_DA = 'DA'; // Danish
    const LANG_DE = 'DE'; // German
    const LANG_EL = 'EL'; // Greek
    const LANG_EN = 'EN'; // English
    const LANG_ES = 'ES'; // Spanish
    const LANG_ET = 'ET'; // Estonian
    const LANG_FI = 'FI'; // Finnish
    const LANG_FR = 'FR'; // French
    const LANG_HU = 'HU'; // Hungarian
    const LANG_IT = 'IT'; // Italian
    const LANG_JA = 'JA'; // Japanese
    const LANG_LT = 'LT'; // Lithuanian
    const LANG_LV = 'LV'; // Latvian
    const LANG_NL = 'NL'; // Dutch
    const LANG_PL = 'PL'; // Polish
    const LANG_PT = 'PT'; // Portuguese
    const LANG_RO = 'RO'; // Romanian
    const LANG_RU = 'RU'; // Russian
    const LANG_SK = 'SK'; // Slovak
    const LANG_SL = 'SL'; // Slovenian
    const LANG_SV = 'SV'; // Swedish
    const LANG_ZH = 'ZH'; // Chinese

    /**
     * Array with all supported language codes
     * @see https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
     */
    const LANG_CODES = [
        self::LANG_AUTO,
        self::LANG_BG,
        self::LANG_CS,
        self::LANG_DA,
        self::LANG_DE,
        self::LANG_EL,
        self::LANG_EN,
        self::LANG_ES,
        self::LANG_ET,
        self::LANG_FI,
        self::LANG_FR,
        self::LANG_HU,
        self::LANG_IT,
        self::LANG_JA,
        self::LANG_LT,
        self::LANG_LV,
        self::LANG_NL,
        self::LANG_PL,
        self::LANG_PT,
        self::LANG_RO,
        self::LANG_RU,
        self::LANG_SK,
        self::LANG_SL,
        self::LANG_SV,
        self::LANG_ZH,
    ];

    /**
     * Array with language codes as keys and the matching language names in English as values
     * @see https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
     */
    const LANG_NAMES = [
        self::LANG_AUTO => 'Auto',
        self::LANG_BG => 'Bulgarian',
        self::LANG_CS => 'Czech',
        self::LANG_DA => 'Danish',
        self::LANG_DE => 'German',
        self::LANG_EL => 'Greek',
        self::LANG_EN => 'English',
        self::LANG_ES => 'Spanish',
        self::LANG_ET => 'Estonian',
        self::LANG_FI => 'Finnish',
        self::LANG_FR => 'French',
        self::LANG_HU => 'Hungarian',
        self::LANG_IT => 'Italian',
        self::LANG_JA => 'Japanese',
        self::LANG_LT => 'Lithuanian',
        self::LANG_LV => 'Latvian',
        self::LANG_NL => 'Dutch',
        self::LANG_PL => 'Polish',
        self::LANG_PT => 'Portuguese',
        self::LANG_RO => 'Romanian',
        self::LANG_RU => 'Russian',
        self::LANG_SK => 'Slovak',
        self::LANG_SL => 'Slovenian',
        self::LANG_SV => 'Swedish',
        self::LANG_ZH => 'Chinese'
    ];

    /**
     * The base URL of the API endpoint
     */
    const API_FREE_BASE_URL = 'https://api-free.deepl.com/v2/';
    const API_PRO_BASE_URL = 'https://api.deepl.com/v2/';

    /**
     * How should the translation engine first split the text into sentences?
     * NONE = No splitting
     * ALL = Split on punctuation and new lines (default)
     * NO_NEW_LINES = Only split on new lines
     */
    const SPLIT_NONE = 0;
    const SPLIT_ALL = 1;
    const SPLIT_NO_NEW_LINES = 1;

    /**
     * Sets whether the translated text should lean towards formal or informal language.
     */
    const FORMALITY_LESS = 'less';
    const FORMALITY_DEFAULT = 'default';
    const FORMALITY_MORE = 'more';

    /**
     * Sets which kind of tags should be handled
     */
    const TAG_HANDLING_UNSET = '';
    const TAG_HANDLING_XML = 'xml';
    const TAG_HANDLING_HTML = 'html';

    /**
     * Current version number
     */
    const VERSION = '2.0.0-alpha';

    /**
     * The DeepL.com API key
     *
     * @var string
     */
    protected string $apiKey = '';

    /**
     * DeepL.com differs between pro and free API.
     * If true, the specified API key indicates that the free API has to be used.
     *
     * @var bool
     */
    protected bool $usesFreeApi = true;

    /**
     * The API base URL according to the plan of the API user (free or pro)
     *
     * @var string
     */
    protected string $apiBaseUrl = '';

    /**
     * ID of an existing glossary
     *
     * @var int|null
     */
    protected int|null $glossaryId = null;

    /**
     * Sets which kind of tags should be handled: xml/xhtml
     *
     * @var string
     */
    protected string $tagHandling = '';

    /**
     * Comma-seperated list of XML tags which never split sentences
     *
     * @var string
     */
    protected string $nonSplittingTags = '';

    /**
     * To disable The automatic detection of the XML structure set this to false
     *
     * @var bool
     */
    protected bool $outlineDetection = true;

    /**
     * Comma-seperated list of XML tags which always cause splits
     *
     * @var string
     */
    protected string $splittingTags = '';

    /**
     * Comma-seperated list of XML tags that indicate text not to be translated
     *
     * @var string
     */
    protected string $ignoreTags = '';

    /**
     * The HTTP client used for communication
     *
     * @var HttpClientInterface
     */
    protected HttpClientInterface $httpClient;

    /**
     * DeepLy object constructor.
     *
     * @param string $apiKey
     */
    public function __construct(string $apiKey)
    {
        $this->setApiKey($apiKey);

        // Create the default HTTP client. You may call setHttpClient() to set another HTTP client.
        $this->httpClient = new CurlHttpClient();
    }

    /**
     * Actually requests a translation from the DeepL.com API
     *
     * @param string      $text             The text you want to translate
     * @param string      $to               Optional: The target language, a self::LANG_<code> constant
     * @param string|null $from             Optional: The source language, a self::LANG_<code> constant
     * @param int         $splitSentences   How should the translation engine split the text into sentences?
     * @param string      $formality        Set whether the translated text should lean towards formal/informal language
     * @param bool        $keepFormatting   How should the translation engine should respect the original formatting?
     * @return \stdClass
     * @throws \Exception|CallException
     */
    protected function requestTranslation(
        string $text,
        string $to,
        ?string $from,
        int $splitSentences = self::SPLIT_ALL,
        string $formality = self::FORMALITY_DEFAULT,
        bool $keepFormatting = false
    ): mixed
    {
        $params = [
            'text' => $text,
            'target_lang' => $to,
            'split_sentences' => $splitSentences,
            'formality' => $formality,
            'preserve_formatting' => $keepFormatting
        ];

        // Set additional parameters if they are not set to their default values
        if ($splitSentences !== self::SPLIT_ALL) {
            $params['split_sentences'] = $splitSentences;
        }
        if ($formality !== self::FORMALITY_DEFAULT) {
            $params['formality'] = $formality;
        }
        if ($keepFormatting !== false) {
            $params['preserve_formatting'] = $keepFormatting;
        }

        // Add even more additional parameters that have been set via self::setSettings();
        if ($this->glossaryId) {
            $params['glossary_id'] = $this->glossaryId;
        }
        if ($this->tagHandling) {
            $params['tag_handling'] = $this->tagHandling;
        }
        if ($this->nonSplittingTags) {
            $params['non_splitting_tags'] = $this->nonSplittingTags;
        }
        if ($this->outlineDetection !== true) {
            $params['outline_detection'] = $this->outlineDetection;
        }
        if ($this->splittingTags) {
            $params['splitting_tags'] = $this->splittingTags;
        }
        if ($this->ignoreTags) {
            $params['ignore_tags'] = $this->ignoreTags;
        }

        // API will attempt to detect the language automatically if the source_lang parameter is not set
        if ($from && $from !== self::LANG_AUTO) {
            $params['source_lang'] = $from;
        }

        $rawResponseData = $this->httpClient->callApi($this->apiBaseUrl . 'translate', $this->apiKey, $params);

        // Make an object from the raw JSON response
        return json_decode($rawResponseData);
    }

    /**
     * Translates a text.
     * ATTENTION: The target language parameter is followed by the source language parameter!
     * This method might throw an exception, so you should wrap it in a try-catch-block.
     *
     * @param string      $text             The text you want to translate
     * @param string      $to               Optional: The target language, a self::LANG_<code> constant
     * @param string|null $from             Optional: The source language, a self::LANG_<code> constant
     * @param int         $splitSentences   How should the translation engine split the text into sentences?
     * @param string      $formality        Set whether the translated text should lean towards formal/informal language
     * @param bool        $keepFormatting   How should the translation engine should respect the original formatting?
     * @return string                       Returns the translated text or null if there is no translation
     * @throws \Exception|CallException
     */
    public function translate(
        string $text,
        string $to = self::LANG_EN,
        ?string $from = self::LANG_AUTO,
        int $splitSentences = self::SPLIT_ALL,
        string $formality = self::FORMALITY_DEFAULT,
        bool $keepFormatting = false
    ): string
    {
        $responseData = $this->requestTranslation($text, $to, $from, $splitSentences, $formality, $keepFormatting);

        // We only return the translations object, the other properties are no longer important
        $translations = $responseData->translations;

        // Combine all translations to one text
        $text = '';
        foreach ($translations as $translation) {
            if ($text) {
                $text .= ' ';
            }
            $text .= $translation->text;
        }

        return $text;
    }

    /**
     * Translates a file that contains plain text (not a .docx or .pdf document!). The $from argument is optional.
     * ATTENTION: The target language parameter is followed by the source language parameter!
     * This method will throw an exception if reading the file or translating fails,
     * so you should wrap it in a try-catch-block.
     *
     * @param string      $filename         The name of the file you want to translate
     * @param string      $to               Optional: The target language, a self::LANG_<code> constant
     * @param string|null $from             Optional: The source language, a self::LANG_<code> constant
     * @param int         $splitSentences   How should the translation engine split the text into sentences?
     * @param string      $formality        Set whether the translated text should lean towards formal/informal language
     * @param bool        $keepFormatting   How should the translation engine should respect the original formatting?
     * @return string                       Returns the translated text or null if there is no translation
     * @throws \Exception|CallException
     */
    public function translateTextFile(
        string $filename,
        string $to = self::LANG_EN,
        ?string $from = self::LANG_AUTO,
        int $splitSentences = self::SPLIT_ALL,
        string $formality = self::FORMALITY_DEFAULT,
        bool $keepFormatting = false
    ): string
    {
        if (! is_readable($filename)) {
            throw new \InvalidArgumentException('Could not read file with the given filename');
        }

        $text = file_get_contents($filename);

        if ($text === false) {
            throw new \RuntimeException(
                'Could not read file with the given filename. Does this file exist and do we have read permission?'
            );
        }

        return $this->translate($text, $to, $from, $splitSentences, $formality, $keepFormatting);
    }

    /**
     * @deprecated Deprecated method for compatibility to version 1. Please use translateTextFile() instead!
     * @throws \Exception
     */
    public function translateFile(string $filename, string $to = self::LANG_EN, ?string $from = self::LANG_AUTO): string
    {
        return $this->translateTextFile($filename,  $to, $from);
    }

    /**
     * Tries to detect the language of a text and returns its language code.
     * The language of the text has to be one of the supported languages or the result will be incorrect.
     * This method might throw an exception, so you should wrap it in a try-catch-block.
     * Especially it will throw an exception if the API was not able to auto-detect the language.
     * ATTENTION: This request increases the usage statistics of your account!
     *
     * @param string       $text The text you want to analyze
     * @return string|null       Returns a language code from the self::LANG_CODES array or null
     * @throws \Exception
     */
    public function detectLanguage(string $text) :? string
    {
        // Note: We always use English as the target language. If the source language is English as well,
        // DeepL automatically seems to set the target language to French so this is not a problem.
        $result = $this->requestTranslation($text, self::LANG_EN, self::LANG_AUTO);

        return $result->translations[0]->detected_source_language;
    }

    /**
     * Returns API usage information
     *
     * @return \stdClass Character_count: Used characters, character_limit: max characters
     */
    public function usage(): \stdClass
    {
        $rawResponseData = $this->httpClient->callApi($this->apiBaseUrl.'usage', $this->apiKey);

        // Make an object from the raw JSON response
        return json_decode($rawResponseData);
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
        return $this->httpClient->ping($this->apiBaseUrl);
    }

    /**
     * Getter for the array with all supported language codes
     *
     * @param bool $withAuto Optional: If true, the 'auto' code will be in the returned array
     * @return string[]
     */
    public function getLangCodes(bool $withAuto = true): array
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
    public function getLangName(string $langCode): string
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
    public function getLangCodeByName(string $langName): string
    {
        if (! in_array($langName, self::LANG_NAMES)) {
            throw new \InvalidArgumentException('The language name is unknown');
        }

        return array_search($langName, self::LANG_NAMES);
    }

    /**
     * Change translation settings.
     * Note that these settings will be applied to EVERY request, this is not a one time thing!
     *
     * @param int|null  $glossaryId ID of an existing glossary, or null
     * @param string    $tagHandling Sets which kind of tags should be handled: "xml"/"xhtml"
     * @param string[]  $nonSplittingTags List of XML tags which never split sentences
     * @param bool      $outlineDetection To disable The automatic detection of the XML structure set this to false
     * @param string[]  $splittingTags List of XML tags which always cause splits
     * @param string[]  $ignoreTags List of XML tags that indicate text not to be translated
     * @return $this
     */
    public function setSettings(
        int $glossaryId = null,
        string $tagHandling = self::TAG_HANDLING_UNSET,
        array $nonSplittingTags = [],
        bool $outlineDetection = true,
        array $splittingTags = [],
        array $ignoreTags = []): static
    {

        $this->glossaryId = $glossaryId;
        $this->tagHandling = $tagHandling;
        $this->nonSplittingTags = join(',', $nonSplittingTags);
        $this->outlineDetection = $outlineDetection;
        $this->splittingTags = join(',', $splittingTags);
        $this->ignoreTags = join(',', $ignoreTags);;

        return $this;
    }

    /**
     * Setter for the API key
     *
     * @param string $apiKey
     * @return void
     */
    public function setApiKey(string $apiKey)
    {
        $this->apiKey = $apiKey;
        $this->usesFreeApi = str_ends_with($this->apiKey, ':fx'); // Free API keys end with ":fx"
        $this->apiBaseUrl = ($this->usesFreeApi ? self::API_FREE_BASE_URL : self::API_PRO_BASE_URL);
    }

}
