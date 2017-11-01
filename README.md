# DeepLy

[![Build Status](https://img.shields.io/travis/chriskonnertz/DeepLy.svg)](https://travis-ci.org/chriskonnertz/DeepLy)
[![Version](https://img.shields.io/packagist/v/chriskonnertz/DeepLy.svg)](https://packagist.org/packages/chriskonnertz/deeply)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/chriskonnertz/deeply/master/LICENSE)


[DeepL.com](https://www.deepl.com/) is a great, new translation service. 
It provides better translations compared to other popular translation engines.
DeepLy is a PHP package that implements a client to interact with DeepL via their _undocumented_ API. 

## Installation

Through [Composer](https://getcomposer.org/):

```
composer require chriskonnertz/deeply
```

From then on you may run `composer update` to get the latest version of this library.

It is possible to use this library without using Composer but then it is necessary to register an 
[autoloader function](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md#example-implementation).

> This library requires PHP 5.6 or higher and the cURL extension.

## Usage Example

```php
$deepLy = new ChrisKonnertz\DeepLy\DeepLy();

$translatedText = $deepLy->translate('Hello world!', 'DE', 'EN');
    
echo $translatedText; // Prints "Hallo Welt!"
```

> An interactive PHP demo script is included. It is located at `demos/demo_translate.php`.

### Sophisticated Example

```php
use ChrisKonnertz\DeepLy\DeepLy;

$deepLy = new DeepLy();

try {
    $translatedText = $deepLy->translate('Hello world!', DeepLy::LANG_EN, DeepLy::LANG_AUTO);
    
    echo $translatedText; // Prints "Hallo Welt!"
} catch (\Exception $exception) {
    echo $exception->getMessage();
}
```

Always wrap calls of the `translate` method in a try-catch-block, because they might throw an exception if the
arguments are invalid or the API call fails. Instead of using hardcoded strings as language arguments 
better use the language code constants of the `DeepLy` class. The class also offers methods such as
`getLangCodes($withAuto = true)` and `supportsLangCode($langCode)`. 

You may use the `proposeTranslations` method if you want to get alternative translations for a text. 
This method cannot operate on more than one sentence at once. 

## Auto-detect language

DeepLy has a method that uses the DeepL API to detect the language of a text:

```php
$languageCode = $deepLy->detectLanguage('Hello world!');
```

This will return 'EN'. The language of the text has to be one of the supported languages or the result will be incorrect.
If you do not need the code of the language but its name, you may call the `$deepLy->getLangName($langCode)` method. 

The API in general can handle and completely translate texts that contain parts with different languages, 
if the language switch is not within a sentence. The `detectLanguage()` method will however 
only return the code of _one_ language. It will throw an exception if it is unable to auto-detect the language. 
This will rarely happen, it is more likely that the API will return a "false positive": It will rather detect the wrong
language than no language at all.

>  An interactive PHP demo script is included. It is located at `demos/demo_detect.php`.

## Supported Languages

DeepL(y) supports these languages:

| Code | Language      |
|------|---------------|
| auto | _Auto detect_ |
| DE   | German        |
| EN   | English       |
| FR   | French        |
| ES   | Spanish       |
| IT   | Italian       |
| NL   | Dutch         |
| PL   | Polish        |

> Note that auto detection only is possible for the source language. 

DeepL says they will [add more languages](https://www.heise.de/newsticker/meldung/Maschinelles-Uebersetzen-Deutsches-Start-up-DeepL-will-230-Sprachkombinationen-unterstuetzen-3836533.html) 
in the future, such as Chinese and Russian.

## Text Length Limit

According to the DeepL.com website, the length of the text that has to be translated is limited to 5000 characters.
Per default DeepLy will throw an exception if the length limit is exceeded. 
You may call `$deepLy->setValidateTextLength(false)` to disable this validation.

## HTTP Client

Per default DeepLy uses a minimalistic HTTP client based on cURL. If you want to use a different HTTP client, 
such as [Guzzle](https://github.com/guzzle/guzzle), create a class that implements the `HttpClient\HttpClientInterface`
 and makes use of the methods of the alternative HTTP client. Then use `$deepLy->setHttpClient($yourHttpClient)`
 to inject it.
 
> Note: If you experience issues with the integrated cURL client that could be solved by setting the
> `CURLOPT_SSL_VERIFYPEER` to `false`, first read this: 
> [snippets.webaware.com.au/../](https://snippets.webaware.com.au/howto/stop-turning-off-curlopt_ssl_verifypeer-and-fix-your-php-config/)
>
> If it does not help try: `$deepLy->getHttpClient()->setSslVerifyPeer(false)`

### Guzzle
 
If you want to use Guzzle as the HTTP client: Support for Guzzle is available out-of-the-box. 
Make sure you have installed Guzzle (preferably via Composer), then copy this code and paste it right after you instantiate DeepLy:

```php
$protocol = $deepLy->getProtocol();
$httpClient = new \ChrisKonnertz\DeepLy\HttpClient\GuzzleHttpClient($protocol);
$deepLy->setHttpClient($httpClient);
```

## Framework Integration

DeepLy comes with support for Laravel 5.x and since it also supports 
[package auto-discovery](https://medium.com/@taylorotwell/package-auto-discovery-in-laravel-5-5-ea9e3ab20518) 
it will be auto-detected in Laravel 5.5. 

In Laravel 5.0-5.4 you manually have to register the service provider
 `ChrisKonnertz\DeepLy\Integrations\Laravel\DeepLyServiceProvider` in the "providers" array and the facade 
 `ChrisKonnertz\DeepLy\Integrations\Laravel\DeepLyFacade` as an alias in the "aliases" array 
 in your `config/app.php` config file.
 
 You can then access DeepLy like this: `$ping = \DeepLy::ping();`
 
## Demos

There are several demo scripts included in the `demos` folder:

* `demo_detect.php`: Demonstrates language detection. Write a text and the API will tell you which language it thinks it is.
* `demo_translate.php`: Demonstrates language translation. Write a text and the API will try to translate it to a language of your choice.
* `demo_split.php`: Demonstrates sentence detection. Write a text and the API will split it into sentences.
* `demo_ping.php`: Demonstrates DeepLy's `ping()` method by pinging the API.


## Current State

I do not know when DeepL.com will officially release their API but I expect them to do it within the next few months. 
Meanwhile you may use this PHP client on your own risk. Also note that their API responds quite slow. This might be intentional.
Nevertheless the API is reliable. I had not a single issue amongst hundreds of API calls.

## Disclaimer

This is not an official package. It is 100% open source and non-commercial. 
The API of DeepL.com is free as well but this [might](https://www.heise.de/newsticker/meldung/Maschinelles-Uebersetzen-Deutsches-Start-up-DeepL-will-230-Sprachkombinationen-unterstuetzen-3836533.html) change in the future.
It remains unclear what this exactly means.

DeepL is a product from DeepL GmbH. More info: [deepl.com/publisher.html](https://www.deepl.com/publisher.html)

This package has been heavily inspired by [node-deepls](https://github.com/pbrln/node-deepl)
and [deeplator](https://github.com/uinput/deeplator). Thank you for your great work! Give these implementations a try if you are coding in Node.js or Python.

## General Notes

* The code of this library is formatted according to the code style defined by the 
[PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) standard.

* Status of this repository: _Maintained_. Create an [issue](https://github.com/chriskonnertz/DeepLy/issues)
and you will get a response, usually within 48 hours.
