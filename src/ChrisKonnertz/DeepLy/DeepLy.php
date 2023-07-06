<?php

namespace ChrisKonnertz\DeepLy;

use ChrisKonnertz\DeepLy\HttpClient\CallException;
use ChrisKonnertz\DeepLy\HttpClient\HttpClientInterface;
use ChrisKonnertz\DeepLy\HttpClient\CurlHttpClient;
use ChrisKonnertz\DeepLy\Models\DocumentHandle;
use ChrisKonnertz\DeepLy\Models\DocumentState;
use ChrisKonnertz\DeepLy\Models\Glossary;
use ChrisKonnertz\DeepLy\Models\Usage;

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
    const LANG_ID = 'ID'; // Indonesian
    const LANG_IT = 'IT'; // Italian
    const LANG_JA = 'JA'; // Japanese
    const LANG_KO = 'KO'; // Korean
    const LANG_LT = 'LT'; // Lithuanian
    const LANG_LV = 'LV'; // Latvian
    const LANG_NB = 'NB'; // Norwegian (Bokmål)
    const LANG_NL = 'NL'; // Dutch
    const LANG_PL = 'PL'; // Polish
    const LANG_PT = 'PT'; // Portuguese
    const LANG_RO = 'RO'; // Romanian
    const LANG_RU = 'RU'; // Russian
    const LANG_SK = 'SK'; // Slovak
    const LANG_SL = 'SL'; // Slovenian
    const LANG_SV = 'SV'; // Swedish
    const LANG_TR = 'TR'; // Turkish
    const LANG_UK = 'UK'; // Ukrainian
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
        self::LANG_ID,
        self::LANG_IT,
        self::LANG_JA,
        self::LANG_KO,
        self::LANG_LT,
        self::LANG_LV,
        self::LANG_NB,
        self::LANG_NL,
        self::LANG_PL,
        self::LANG_PT,
        self::LANG_RO,
        self::LANG_RU,
        self::LANG_SK,
        self::LANG_SL,
        self::LANG_SV,
        self::LANG_TR,
        self::LANG_UK,
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
        self::LANG_ID => 'Indonesian',
        self::LANG_IT => 'Italian',
        self::LANG_JA => 'Japanese',
        self::LANG_KO => 'Korean',
        self::LANG_LT => 'Lithuanian',
        self::LANG_LV => 'Latvian',
        self::LANG_NB => 'Norwegian', // (Bokmål)  
        self::LANG_NL => 'Dutch',
        self::LANG_PL => 'Polish',
        self::LANG_PT => 'Portuguese',
        self::LANG_RO => 'Romanian',
        self::LANG_RU => 'Russian',
        self::LANG_SK => 'Slovak',
        self::LANG_SL => 'Slovenian',
        self::LANG_SV => 'Swedish',  
        self::LANG_TR => 'Turkish',          
        self::LANG_UK => 'Ukrainian',
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
    const TAG_HANDLING_UNSET = null;
    const TAG_HANDLING_XML = 'xml';
    const TAG_HANDLING_HTML = 'html';

    /**
     * Current version number
     */
    const VERSION = '2.2.0';

    /**
     * The DeepL.com API key.
     * @see setApiKey()
     *
     * @var string
     */
    protected string $apiKey = '';

    /**
     * DeepL.com differs between pro and free API.
     * If true, the specified API key indicates that the free API has to be used.
     * @see getApiKeyType()
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
     * Unique identifier of an existing glossary (not to be confused with the name!)
     *
     * @var string|null
     */
    protected string|null $glossaryId = null;

    /**
     * Sets which kind of tags should be handled: xml/xhtml
     *
     * @var string|null
     */
    protected string|null $tagHandling = null;

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
     * @param string                   $apiKey     Initially set the API key
     * @param HttpClientInterface|null $httpClient Inject your own HTTP client if necessary
     */
    public function __construct(string $apiKey, HttpClientInterface $httpClient = null)
    {
        // Initially set the API key. You can also use an empty string and call setApiKey() later,
        // but that's not not recommended.
        $this->setApiKey($apiKey);

        // Create the default HTTP client if necessary.
        // You may call setHttpClient() to set another HTTP client.
        $this->httpClient = $httpClient ?? new CurlHttpClient();
    }

    /**
     * Do an API call to the DeepL.com API
     *
     * @param string  $function  The API function
     * @param array   $params    The payload of the request. Will be encoded as JSON
     * @param string  $method    The request method ('GET', 'POST', 'DELETE')
     * @param ?string $filename  The filename of a file that should be uploaded
     * @param bool    $parseJson If true, parse the result of the API call and return a \stdClass. Else: return string
     * @return \stdClass|string|null
     */
    protected function callApi(
        string $function,
        array $params = [],
        string $method = HttpClientInterface::METHOD_POST,
        string $filename = null,
        bool $parseJson = true
    ) : \stdClass|string|null
    {
        // Do the actual API call via HTTP client
        $rawResponseData = $this->httpClient->callApi(
            $this->apiBaseUrl.$function, $this->apiKey, $params, $method, $filename
        );

        // Make an object from the raw JSON response
        return $parseJson ? json_decode($rawResponseData) : $rawResponseData;
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
     * @return \stdClass|string|null
     * @throws \Exception|CallException
     */
    protected function requestTranslation(
        string $text,
        string $to,
        ?string $from,
        int $splitSentences = self::SPLIT_ALL,
        string $formality = self::FORMALITY_DEFAULT,
        bool $keepFormatting = false
    ): \stdClass|string|null
    {
        $params = [
            'text' => $text,
            'target_lang' => $to
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

        return $this->callApi('translate', $params);
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
     * @deprecated ⚠️ Method has been replaced!
     *             Please replace with:
     *                 $text = file_get_contents($filename);
     *                 $this->translate($text);
     *
     * @throws \Exception
     */
    public function translateFile(
        string $filename,
        string $to = self::LANG_EN,
        ?string $from = self::LANG_AUTO,
        int $splitSentences = self::SPLIT_ALL,
        string $formality = self::FORMALITY_DEFAULT,
        bool $keepFormatting = false
    ): string
    {
         throw new \Exception('This method is no longer supported.');
    }

    /**
     * Tries to detect the language of a text and returns its language code.
     * The language of the text has to be one of the supported languages or the result will be incorrect.
     * This method might throw an exception, so you should wrap it in a try-catch-block.
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
     * Get a list with information about all your glossaries
     *
     * @return Glossary[]
     * @throws CallException
     */
    public function getGlossaries() : array
    {
        $originalGlossaries = $this->callApi('glossaries', [], HttpClientInterface::METHOD_GET);

        $glossaries = [];
        foreach ($originalGlossaries->glossaries as $originalGlossary) {
            $glossaries[] = new Glossary($originalGlossary);
        }

        return $glossaries;
    }

    /**
     * Get information about a specific glossary
     *
     * @param string $glossaryId The unique identifier of an exising glossary (not to be confused with the name!)
     * @return Glossary          Information about the glossary
     * @throws CallException     Especially thrown if no glossary with the given glossary exists
     */
    public function getGlossary(string $glossaryId) : Glossary
    {
        $data = $this->callApi('glossaries/'.$glossaryId, [], HttpClientInterface::METHOD_GET);

        return new Glossary($data);
    }

    /**
     * Create a new glossary with entries.
     * The entries array has to consist of items with the original text as item key and the translation as item value.
     *
     * @param string   $name    The name of the glossary (nopt to be confused with the unique identifier!)
     * @param string   $to      The target language, a self::LANG_<code> constant
     * @param string   $from    The source language, a self::LANG_<code> constant
     * @param string[] $entries The entries of the glossary. Item key = original text, item value = translation
     * @return Glossary         Information about the glossary
     * @throws CallException
     */
    public function createGlossary(string $name, string $to, string $from, array $entries) : Glossary
    {
        // The API expects the entries in a "tab seperated" string format, so lets build that string
        $entriesEncoded = '';
        array_walk($entries, function($item, $key) use (&$entriesEncoded) {
            if ($entriesEncoded) {
                $entriesEncoded .= "\n"; // Separate entries by a new line character
            }

            // Leading/trailing whitespace is not allowed. Tabs are used to separate the translations.
            $entriesEncoded .= trim($key)."\t".trim($item);
        });

        $params = [
            'name' => $name,
            'source_lang' => $from,
            'target_lang' => $to,
            'entries' => $entriesEncoded,
            'entries_format' => 'tsv', // Note: currently 'tsv' is the only available format
        ];

        $data = $this->callApi('glossaries', $params);

        return new Glossary($data);
    }

    /**
     * Deletes an existing glossary
     *
     * @param string $glossaryId The unique identifier of an exising glossary (not to be confused with the name!)
     * @throws CallException
     */
    public function deleteGlossary(string $glossaryId)
    {
        $this->callApi('glossaries/'.$glossaryId, [], HttpClientInterface::METHOD_DELETE);
    }

    /**
     * Get the translation entries of a specific glossary.
     * The result is an array of items, with the original text as item key and the translation as item value.
     *
     * @param string $glossaryId The unique identifier of an exising glossary (not to be confused with the name!)
     * @return string[]
     * @throws CallException     Especially thrown if no glossary with the given glossary exists
     */
    public function getGlossaryEntries(string $glossaryId) : array
    {
        $rawEntries = $this->callApi(
            'glossaries/'.$glossaryId.'/entries', [], HttpClientInterface::METHOD_GET, null, false
        );

        // The API provides the entries in a "tab seperated" string format, so lets parse that string
        $entries = [];
        $rawEntries = explode("\n", $rawEntries); // Entries are seperated by a new line character
        foreach ($rawEntries as $rawEntry) {
            $parts = explode("\t", $rawEntry); // Tabs are used to separate the translations.
            $entries[$parts[0]] = $parts[1];
        }

        return $entries;
    }

    /**
     * Upload a document for translation.
     * Afterwards it will be processed (translated).
     * Note: The maximum upload limit for any document is 10 MB and 1.000.000 characters.
     * ATTENTION: Every file upload is at least billed with 50.000 characters!
     *
     * @param string      $filename  The name of the file
     * @param string      $to        The target language, a self::LANG_<code> constant
     * @param string|null $from      The source language, a self::LANG_<code> constant
     * @param string      $formality Set whether the translated text should lean towards formal/informal language
     * @return DocumentHandle        Properties: documentId and documentKey
     * @throws CallException
     */
    public function uploadDocument(
        string $filename,
        string $to,
        ?string $from = self::LANG_AUTO,
        string $formality = self::FORMALITY_DEFAULT
    ) : DocumentHandle
    {
        $params = [
             'target_lang' => $to
        ];

        if ($formality !== self::FORMALITY_DEFAULT) {
            $params['formality'] = $formality;
        }

        // Add even more additional parameters that have been set via self::setSettings();
        if ($this->glossaryId) {
            $params['glossary_id'] = $this->glossaryId;
        }

        // API will attempt to detect the language automatically if the source_lang parameter is not set
        if ($from && $from !== self::LANG_AUTO) {
            $params['source_lang'] = $from;
        }

        $data = $this->callApi('document', $params, HttpClientInterface::METHOD_POST, $filename);

        return new DocumentHandle($data);
    }

    /**
     * Get information about an uploaded document, especially the state of the translation.
     *
     * @param string $documentId  The unique identifier of the document
     * @param string $documentKey The document encryption key that was sent to the client after uploading the document
     * @return DocumentState      Properties: documentId, status, secondsRemaining, billedCharacters
     * @throws CallException
     */
    public function getDocumentState(string $documentId, string $documentKey) : DocumentState
    {
        $data = $this->callApi('document/'.$documentId, ['document_key' => $documentKey]);

        return new DocumentState($data);
    }

    /**
     * Download an uploaded document after it has been processed (translated).
     * ATTENTION: A document can be downloaded only once!
     *
     * @param string  $documentId  The unique identifier of the document
     * @param string  $documentKey The document encryption key that was sent to the client after uploading the document
     * @param ?string $filename    Optional: Specify a filename if you want to save the file contents to a file
     * @return string              Returns the raw file contents
     * @throws CallException
     */
    public function downloadDocument(string $documentId, string $documentKey, string $filename = null) : string
    {
        $contents = $this->callApi(
            'document/'.$documentId.'/result',
            ['document_key' => $documentKey],
            HttpClientInterface::METHOD_POST,
            null,
            false
        );

        if ($filename) {
            file_put_contents($filename, $contents);
        }

        return $contents;
    }

    /**
     * Returns API usage information
     *
     * @return Usage Properties:
     *                   characterCount = characters translated so far in the current billing period
     *                   characterLimit = current maximum number of characters that can be translated per billing period
     *                   characterQuota = usage (0-1 / null)
     *                   documentCount = documents translated so far in the current billing period if unknown
     *                   documentLimit = current maximum number of documents that can be translated per billing period
     *                   documentQuota = usage (0-1 / null)
     *                   teamDocumentCount = docs translated by all users in the team so far in the billing period
     *                   teamDocumentLimit = max number of docs that can be translated by the team per billing period
     *                   teamDocumentQuota = usage (0-1 / null)
     */
    public function usage(): Usage
    {
        $data = $this->callApi('usage');

        return new Usage($data);
    }

    /**
     * Pings the API server. Returns the duration in seconds until the response arrives
     * or throws an exception if no valid response was received.
     *
     * @return float
     * @throws CallException
     */
    public function ping() : float
    {
        return $this->httpClient->ping($this->apiBaseUrl);
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
    public function supportsLangCode(string $langCode, bool $allowAuto = false) : bool
    {
        return in_array($langCode, $this->getLangCodes($allowAuto));
    }

    /**
     * Getter for the array with all supported language codes
     *
     * @param bool $withAuto Optional: If true, the 'auto' code will be in the returned array
     * @return string[]
     */
    public function getLangCodes(bool $withAuto = true): array
    {

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
     * Reset all translation settings to their default values.
     */
    public function resetSettings()
    {
        return $this->setSettings();
    }

    /**
     * Change translation settings.
     * Note that these settings will be applied to EVERY request, this is not a one time thing!
     * To reset all settings to their defaults, simply call the resetSettings() method.
     *
     * @param string|null $glossaryId Unique identifier of an existing glossary, or null
     * @param string|null $tagHandling Sets which kind of tags should be handled: "xml"/"xhtml"
     * @param string[]    $nonSplittingTags List of XML tags which never split sentences
     * @param bool        $outlineDetection To disable The automatic detection of the XML structure set this to false
     * @param string[]    $splittingTags List of XML tags which always cause splits
     * @param string[]    $ignoreTags List of XML tags that indicate text not to be translated
     * @return $this
     */
    public function setSettings(
        string $glossaryId = null,
        string|null $tagHandling = self::TAG_HANDLING_UNSET,
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
        $this->usesFreeApi = $this->getApiKeyType();
        $this->apiBaseUrl = ($this->usesFreeApi ? self::API_FREE_BASE_URL : self::API_PRO_BASE_URL);
    }

    /**
     * Returns true if the provided key is a key for the free API, and returns false if it is a key for the pro API.
     *
     * @return bool
     */
    public function getApiKeyType() : bool
    {
        // Free API keys end with ":fx"
        return str_ends_with($this->apiKey, ':fx');
    }

    /**
     * Getter for the HTTP client object
     *
     * @return HttpClientInterface
     */
    public function getHttpClient() : HttpClientInterface
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

}
