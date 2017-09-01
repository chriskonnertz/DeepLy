# DeepLy

[![Build Status](https://travis-ci.org/chriskonnertz/DeepLy.png)](https://travis-ci.org/chriskonnertz/DeepLy)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/chriskonnertz/deeply/master/LICENSE)

[DeepL.com](https://www.deepl.com/) is a great, new translation service. 
It provides better translations compared to other popular translation engines.
DeepLy is a PHP package that implements a client to interact with DeepL via their _undocumented_ API. 
I do not know if or when they will officially publish their API but I expect them to do it. 
Meanwhile you may use this PHP client implementation to prepare your project for the official API release.

## Installation

Through Composer:

```
composer require chriskonnertz/deeply
```

From then on you may run `composer update` to get the latest version of this library.

It is possible to use this library without using Composer but then it is necessary to register an 
[autoloader function](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md#example-implementation).

> This library requires PHP 5.6 or higher and the cURL extension.

## Usage example

```php
$deepLy = new ChrisKonnertz\DeepLy\DeepLy();

$translatedText = $deepLy->translate('Hello world!', 'DE', 'EN');

echo $translatedText; // Prints "Hallo Welt!"
```

> There is a PHP demo script included. It is located at `dev/demo.php`.

## Supported Languages

DeepL(y) supports these languages:

| Code | Language |
|----|----|
| DE | German |
| EN | English |
| FR | French |
| ES | Spanish |
| IT | Italian |
| NL | Dutch |
| PL | Polish|

## Current State

I tried to rush for a first release. It works and I hope that I do not have to change the main method `translate()`
in a way that makes it incompatible with the current release. However, I cannot guarantee that. 
More commits will come soon. There is a lot to refactor and improve.

## Disclaimer

This is not an official package. It will be 100% open source and non-commercial. 
DeepL is a product from DeepL GmbH. More info: [deepl.com/publisher.html](https://www.deepl.com/publisher.html)

This package has been heavily inspired by [node-deepls](https://www.npmjs.com/package/node-deepl).
Thank you for your great work!

## General Notes

* The code of this library is formatted according to the code style defined by the 
[PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) standard.

* Status of this repository: _Maintained_. Create an [issue](https://github.com/chriskonnertz/DeepLy/issues)
and you will get a response, usually within 48 hours.
