![alt text](https://i.imgur.com/nWPtAKg.png "DeepLy Logo")

# DeepLy 2

[![Build Status](https://img.shields.io/github/actions/workflow/status/chriskonnertz/DeepLy/php.yml?style=flat-square)](https://github.com/chriskonnertz/DeepLy/actions)
[![Version](https://img.shields.io/packagist/v/chriskonnertz/DeepLy.svg?style=flat-square)](https://packagist.org/packages/chriskonnertz/deeply)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](https://raw.githubusercontent.com/chriskonnertz/deeply/master/LICENSE)
[![Version](https://img.shields.io/packagist/dt/chriskonnertz/deeply?style=flat-square)](https://packagist.org/packages/chriskonnertz/deeply)

[DeepL](https://www.deepl.com/) is a next-generation translation service. 
DeepLy is a dependency-free PHP library that implements a client to interact with the 
[DeepL API](https://www.deepl.com/docs-api) using an API key. 
You can get an API key for free on their [website](https://www.deepl.com/). 
DeepLy automatically supports both the free and the pro API.
For interactive demo scripts take a look at the [demos folder](demos).

## Installation

This library requires PHP 8.0 or higher and the cURL extension. Install DeepLy trough [Composer](https://getcomposer.org/):

```
composer require chriskonnertz/deeply
```

## Examples

```php
$deepLy = new ChrisKonnertz\DeepLy\DeepLy('your-api-key');

$translatedText = $deepLy->translate('Hello world!', 'DE');
    
echo $translatedText; // Prints "Hallo Welt!"
```

> 💡 An interactive PHP demo script is included. It is located at [demos/demo_translate.php](demos/demo_translate.php).

### Sophisticated Example

```php
$deepLy = new ChrisKonnertz\DeepLy\DeepLy('your-api-key');

try {
    $translatedText = $deepLy->translate('Hello world!', DeepLy::LANG_EN, DeepLy::LANG_AUTO);
    
    echo $translatedText; // Prints "Hallo Welt!"
} catch (\Exception $exception) {
    echo $exception->getMessage();
}
```

Always wrap calls of the `translate` method in a try-catch-block, because they might throw an exception if the
arguments are invalid or the API call fails. The exception will have an explanatory message and a specific error code. 

Instead of using hardcoded strings as language arguments 
better use the language code constants of the `DeepLy` class. The class also offers methods such as
`getLangCodes($withAuto = true)` and `supportsLangCode($langCode)`. 

If you need to specify advanced settings, use the `setSettings()` method: `$deepLy->setSettings($glossaryId);`

## Auto-Detect Language

> ⚠️ ATTENTION: Using this method increases the usage statistics of your account!

DeepLy has a method that uses the DeepL API to detect the language of a text:

```php
$languageCode = $deepLy->detectLanguage('Hello world!');
```

This will return 'EN'. The language of the text has to be one of the supported languages or the result will be incorrect.
If you do not need the code of the language but its English name, you may call the `$deepLy->getLangName($langCode)` method. 

The API, in general, can handle and completely translate texts that contain parts with different languages, 
if the language switch is not within a sentence. The `detectLanguage()` method will however 
only return the code of _one_ language. It will throw an exception if it is unable to auto-detect the language. 
This will rarely happen, it is more likely that the API will return a "false positive": It will rather detect the wrong
language than no language at all.

> 💡 An interactive PHP demo script is included. It is located at [demos/demo_detect](demos/demo_detect.php).

## Supported Languages

DeepL(y) supports these languages:

| Code | Language      |     | Code  | Language      |
|------|---------------|-----|-------|---------------|
| auto | _Auto detect_ |     | KO    | Korean        |
| ID   | Indonesian    |     | TR    | Turkish       |
| IT   | Italian       |     | ZH    | Chinese       |
| BG   | Bulgarian     |     | LT    | Lithuanian    |
| CS   | Czech         |     | LV    | Latvian       |
| DA   | Danish        |     | NB    | Norwegian     |
| DE   | German        |     | NL    | Dutch         |
| EL   | Greek         |     | PL    | Polish        |
| EN   | English       |     | PT    | Portuguese    |
| ES   | Spanish       |     | RO    | Romanian      |
| ET   | Estonian      |     | RU    | Russian       |
| FI   | Finnish       |     | SK    | Slovak        |
| PT   | French        |     | SL    | Slovenian     |
| HU   | Hungarian     |     | SV    | Swedish       |
| JA   | Japanese      |     |       |               |

> 💡 Note that only the source language can be auto-detected.

## Glossaries

To get a list with information about all your glossaries, do:

```php
$glossaries = $deepLy->getGlossaries();
print_r($glossaries); // Prints an array with Glossary objects
```
Output:
```
Array
(
    [0] => ChrisKonnertz\DeepLy\Models\Glossary Object
        (
            [glossaryId] => 56cab399-ac8e-4a57-aadc-fa95103f2de5
            [entryCount] => 2
            ...
        )
    [2] => ChrisKonnertz\DeepLy\Models\Glossary Object
        (
            [glossaryId] => d9eb53b5-3929-49a1-b5e1-df1eb8be93c9
            [entryCount] => 5
            ...
        )
)
```

To get information about a specific glossary, do:

```php
$glossary = $deepLy->getGlossary('your-glossary-id');
print_r($glossary); // Prints a \stdClass
```
Output:
```
ChrisKonnertz\DeepLy\Models\Glossary Object
(
    [glossaryId] => d9eb53b5-3929-49a1-b5e1-df1eb8be93c9
    [name] => DeepLy Test
    [ready] => 1
    [from] => en
    [to] => de
    [creationTimeIso] => 2022-04-21T17:46:31.83913+00:00
    [creationDateTime] => DateTime Object
    [entryCount] => 2
)
```

To get the translation entries of a specific glossary, do:

```php
$entries = $deepLy->getGlossaryEntries('your-glossary-id');
print_r($entries);  // Prints an array with string items
```
Output:
```
Array
(
    [Entry 1 DE] => Entry 1 EN
    [Entry 2 DE] => Entry 2 EN
)
```

To create a new glossary with translation entries, do:

```php
$glossary = $deepLy->createGlossary('test', 'de', 'en', ['Example DE' => 'Example EN']);
```

To delete an existing glossary, do:

```php
$deepLy->deleteGlossary('your-glossary-id');
```

> 💡 An interactive PHP demo script is included. It is located at [demos/demo_glossaries.php](demos/demo_glossaries.php).

## Documents

Translating documents consists of three steps. The first step is to upload a document:
```php
$filename = __DIR__.'/test_document_original.pdf';
$result = $deepLy->uploadDocument($filename, 'DE');

var_dump($result);
```
Output:
```
ChrisKonnertz\DeepLy\Models\DocumentHandle Object
(
  [documentId] => D014F316B7A173079074BE76F530F846
  [documentKey] => 39FF8B10D20621096F23BF96CC103E12074727007C62963CF49AE8A9965D7695
)
```
> 💡 The maximum upload limit for any document is 10 MB and 1.000.000 characters.
> 
> ⚡ Every file upload is at least billed with 50.000 characters!

The second step is to wait for the DeepL.com API to finish processing (translating) the document.
You can check the state:
```php
$result = $deepLy->getDocumentState($result->documentId, $result->documentKey);

var_dump($result);
```
Output:
```
ChrisKonnertz\DeepLy\Models\DocumentState Object
(
    [documentId] => D014F316B7A173079074BE76F530F846
    [status] => done
    [billedCharacters] => 50000
    [secondsRemaining] => null
)
```
In this case the document has been processed. 
This is indicated by "status" being "done" and "seconds_remaining" being *null*.

> 💡 The document life cycle is: *queued* ➜ *translating* ➜ *done* (or *error*)
> 
> There are constants that you can use to check these values: `DocumentState\STATUS_DONE` etc.

The third step is to download the document:
```php
$deepLy->downloadDocument($documentId, $documentKey, 'test_document_translated.pdf');
```
If you do not want to store the file, do:
```php
$contents = $deepLy->downloadDocument($documentId, $documentKey);
```

> ⚡ A document can only be downloaded once!

> 💡 An interactive PHP demo script is included. It is located at [demos/demo_documents.php](demos/demo_documents.php).

## Usage Statistic

To get usage statistics, do:

```php
$usage = $deepLy->usage(); // Returns an object of type "Usage"

echo $usage->characterCount.'/'.$usage->characterLimit
    . ' characters ('.round($usage->characterQuota * 100).'%)';
```

Depending on the user account type, some usage types will be null.
Learn more: https://www.deepl.com/de/docs-api/other-functions/monitoring-usage/

## Framework Integration

DeepLy comes with support for Laravel 5.5+ and since it also supports 
[package auto-discovery](https://medium.com/@taylorotwell/package-auto-discovery-in-laravel-5-5-ea9e3ab20518) 
it will be auto-detected. However, you have to store your DeepL API key manually in the `.env` file, like this:
```
DEEPL_API_KEY = xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
```
 
Afterwards you can access DeepLy like this: `$ping = \DeepLy::ping();`

## HTTP Client

Per default DeepLy uses a minimalistic HTTP client based on cURL. If you want to use a different HTTP client,
such as [Guzzle](https://github.com/guzzle/guzzle), create a class that implements the `HttpClient\HttpClientInterface`
and makes use of the methods of the alternative HTTP client. Then use `$deepLy->setHttpClient($yourHttpClient)`
to inject it.

> 💡 Note: If you experience issues with the integrated cURL client that could be solved by setting the
> `CURLOPT_SSL_VERIFYPEER` to `false`, first read this:
> [snippets.webaware.com.au/../](https://snippets.webaware.com.au/howto/stop-turning-off-curlopt_ssl_verifypeer-and-fix-your-php-config/)
>
> If it does not help try: `$deepLy->getHttpClient()->setSslVerifyPeer(false)`

> 💡 You can set up a proxy with: `$deepLy->getHttpClient()->setProxy('ip:port', 'user:password')`


## Tests

Export your API key:
```
export DEEPL_API_KEY=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
```
Run `composer install` from the DeepLy directory, then run the tests:
```
./vendor/phpunit/phpunit/phpunit
```

## Differences to V1

To upgrade from v1 to v2, make sure you specify the API key when instantiating the DeepLy object.
Apart from the changes mentioned above your v1 code should still work with v2 as long 
as you did not write your own HTTP client or extended the DeepLy class with a custom class.
To learn more about the changes, please take a look at the [changelog](CHANGELOG.md).

## Disclaimer

This is not an official package. It is 100% open source and non-commercial.

DeepL is a product of DeepL GmbH. More info: [deepl.com/publisher.html](https://www.deepl.com/publisher.html)

## Notes

* Texts have to be UTF8-encoded.

* If you are looking for a real-world example application that uses DeepLy, you may take a look at [Translation Factory](https://github.com/chriskonnertz/translation-factory).

* The code of this library is formatted according to the code style defined by the  
[PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) standard.

* Status of this repository: _Maintained_. Create an [issue](https://github.com/chriskonnertz/DeepLy/issues)
and you will get a response, usually within 48 hours.
