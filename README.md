# DeepLy

[![Build Status](https://img.shields.io/travis/chriskonnertz/DeepLy.svg)](https://travis-ci.org/chriskonnertz/DeepLy)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/chriskonnertz/deeply/master/LICENSE)
[![Version](https://img.shields.io/packagist/v/chriskonnertz/DeepLy.svg)](https://packagist.org/packages/chriskonnertz/deeply)


[DeepL.com](https://www.deepl.com/) is a great, new translation service. 
It provides better translations compared to other popular translation engines.
DeepLy is a PHP package that implements a client to interact with DeepL via their _undocumented_ API. 

## Installation

Through Composer:

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

> There is an interactive PHP demo script included. It is located at `demos/demo.php`.

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

## HTTP Client

Per default DeepLy uses a minimalistic HTTP client based on cURL. If you want to use a different HTTP client, 
such as [Guzzle](https://github.com/guzzle/guzzle), create a class that implements the `HttpClient\HttpClientInterface`
 and makes use of the methods of the alternative HTTP client. Then use `$deepLy->setHttpClient($yourHttpClient)`
 to inject it.
 
Support for Guzzle is available out-of-the-box. Make sure you have installed Guzzle (preferably via Composer), 
then copy this code and paste it right after you instantiate DeepLy:

```php
$protocol = $deepLy->getProtocol();
$httpClient = new \ChrisKonnertz\DeepLy\HttpClient\GuzzleHttpClient($protocol);
$deepLy->setHttpClient($httpClient);
```
 
> Note: If you experience issues with the integrated cURL client that could be solved by setting the
> `CURLOPT_SSL_VERIFYPEER` to `false`, first read this: 
> [snippets.webaware.com.au/../](https://snippets.webaware.com.au/howto/stop-turning-off-curlopt_ssl_verifypeer-and-fix-your-php-config/)
>
> If it does not help try: `$deepLy->getHttpClient()->setSslVerifyPeer(false)`

## Text Length Limit

According to the DeepL.com website, the length of the text that has to be translated is limited to 5000 characters.
Per default DeepLy will throw an exception if the length limit is exceeded. 
You may call `$deepLy->setValidateTextLength(false)` to disable that validation.

## Current State

I do not know if or when DeepL.com will officially release their API but I expect them to do it at some point. 
Meanwhile you may use this PHP client on your own risk. Also note that their API responds quite slow. This might be intentional.
Nevertheless the API is reliable. I had not a single issue amongst hundreds of API calls.

I tried to rush towards a first release. It works and I hope that I do not have to change the main method `translate()`
in a way that makes it incompatible with the current release. However, I cannot guarantee that. 
More commits might come soon. There still is space for [improvements](https://github.com/chriskonnertz/DeepLy/issues/1).

## Disclaimer

This is not an official package. It is 100% open source and non-commercial. The API of DeepL.com is free as well but this might change in the future.

DeepL is a product from DeepL GmbH. More info: [deepl.com/publisher.html](https://www.deepl.com/publisher.html)

This package has been heavily inspired by [node-deepls](https://github.com/pbrln/node-deepl)
and [deeplator](https://github.com/uinput/deeplator). Thank you for your great work!

## General Notes

* The code of this library is formatted according to the code style defined by the 
[PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) standard.

* Status of this repository: _Maintained_. Create an [issue](https://github.com/chriskonnertz/DeepLy/issues)
and you will get a response, usually within 48 hours.
