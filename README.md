# DeepLy 2

[![Build Status](https://img.shields.io/travis/chriskonnertz/DeepLy.svg?style=flat-square)](https://travis-ci.org/chriskonnertz/DeepLy)
[![Version](https://img.shields.io/packagist/v/chriskonnertz/DeepLy.svg?style=flat-square)](https://packagist.org/packages/chriskonnertz/regex)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](https://raw.githubusercontent.com/chriskonnertz/deeply/master/LICENSE)

> ⛔ ATTENTION: This is a beta branch. Ready to be tested, not ready to be used in production!

[DeepL.com](https://www.deepl.com/) is a next-generation translation service. 
It provides better translations compared to other popular translation engines.
DeepLy is a PHP package that implements a client to interact with DeepL via their API using an API key. 
You can get an API key for free on their website. DeepLy supports both the free and the pro API and will 
automatically target the correct API end point. There are several demo scripts included in the demos folder 
that show how to use DeepLy.

## Installation

This library requires PHP 8.0 or higher and the cURL extension. Install trough [Composer](https://getcomposer.org/):

```
composer require chriskonnertz/deeply
```

## Example

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

> 💡 An interactive PHP demo script is included. It is located at [demos/demo_glossaries.demo_detect](demos/demo_detect.php).

## Supported Languages

DeepL(y) supports these languages:

| Code | Language      |     | Code  | Language      |
|------|---------------|-----|-------|---------------|
| auto | _Auto detect_ |     |       |               |
| IT   | Italian       |     | JA    | Japanese      |
| BG   | Bulgarian     |     | LT    | Lithuanian    |
| CS   | Czech         |     | LV    | Latvian       |
| DA   | Danish        |     | NL    | Dutch         |
| DE   | German        |     | PL    | Polish        |
| EL   | Greek         |     | PT    | Portuguese    |
| EN   | English       |     | RO    | Romanian      |
| ES   | Spanish       |     | RU    | Russian       |
| ET   | Estonian      |     | SK    | Slovak        |
| FI   | Finnish       |     | SL    | Slovenian     |
| PT   | French        |     | SV    | Swedish       |
| HU   | Hungarian     |     | ZH    | Chinese       |

> 💡 Note that auto-detection only is possible for the source language.

## Glossaries

To get a list with information about all your glossaries, do:

```php
$glossaries = $deepLy->getGlossaries();
print_r($glossaries); // Prints an array with \stdClass items
```

To get information about a specific glossary, do:

```php
$glossary = $deepLy->getGlossary('your-glossary-id');
print_r($glossary); // Prints a \stdClass
```

To get the translation entries of a specific glossary, do:

```php
$entries = $deepLy->getGlossaryEntries('your-glossary-id');
print_r($entries);  // Prints an array with string items
```

To create a new glossary with translation entries, do:

```php
$deepLy->createGlossary('test', 'de', 'en', ['Example DE' => 'Example EN']);
```

To delete an existing glossary, do:

```php
$deepLy->deleteGlossary('your-glossary-id');
```

> 💡 An interactive PHP demo script is included. It is located at [demos/demo_glossaries.php](demos/demo_glossaries.php).

## Usage

To get usage statistics, do:

```php
$usage = $deepLy->usage();

echo $usage->characterCount.'/'.$usage->characterLimit
    . ' characters ('.round($usage->characterQuota * 100).'%)';
```

Depending on the user account type, some usage types will be null.
Learn more: https://www.deepl.com/de/docs-api/other-functions/monitoring-usage/

## Framework Integration

DeepLy comes with support for Laravel 5.5+ and since it also supports 
[package auto-discovery](https://medium.com/@taylorotwell/package-auto-discovery-in-laravel-5-5-ea9e3ab20518) 
it will be auto-detected. 

You have to store your DeepL API key in the `.env` file like this:
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

## Differences to V1

Differences to DeepLy version 1.x are:
- Text length check has been removed (because the limit is now about 120000 letters which should be enough in most cases)
- Texts can no longer be split into sentences, as the API does not seem to support this
- `proposeTranslations()` method has been removed
- Guzzle implementation removed (you can still write your own though)
- JSON RPC protocol support has been removed
- All the bag classes have been removed
- No longer uses the unofficial API, but uses official v2 API
- API key has been introduced
- Updated API error handling, `CallException` now contains API HTTP error code
- Glossary support has been introduced
- Usage method has been introduced
- Support for new languages added
- The `translateFile()` method is now deprecated, please use `translateTextFile()` instead!

To upgrade from v1 to v2, make sure you specify the API key when instantiating the DeepLy object.
Apart from the changes mentioned above your v1 code should still work with v2.

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
