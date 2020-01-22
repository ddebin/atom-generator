[![Travis (.org)](https://img.shields.io/travis/ddebin/atom-generator?logo=travis&style=for-the-badge)](https://travis-ci.org/ddebin/atom-generator)
[![Codecov](https://img.shields.io/codecov/c/github/ddebin/atom-generator?logo=codecov&style=for-the-badge)](https://codecov.io/gh/ddebin/atom-generator)
![PHP from Packagist](https://img.shields.io/packagist/php-v/ddebin/atom-generator?logo=php&style=for-the-badge)
[![Packagist Version](https://img.shields.io/packagist/v/ddebin/atom-generator?style=for-the-badge)](https://packagist.org/packages/ddebin/atom-generator)
![Packagist](https://img.shields.io/packagist/l/ddebin/atom-generator?style=for-the-badge)

# Atom feed generator

This library is an [Atom](https://en.wikipedia.org/wiki/Atom_(Web_standard) feed generator, PHP 7.1+, fully typed ([PHPStan](https://github.com/phpstan/phpstan) level 7, 100% code coverage). Follows W3C standard
([RFC 4287](https://validator.w3.org/feed/docs/rfc4287.html)).

## Installing

Install via [Composer](https://getcomposer.org/):

```bash
composer require ddebin/atom-generator
```

## Example

```php
<?php

include_once 'vendor/autoload.php';

$entry = new AtomGenerator\Entry();
$entry->setTitle('Post', 'text');
$entry->setId('tag:id');
$entry->setContent('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'text');
$entry->setUpdatedDateTime(new DateTime('2019-05-04T21:00:40Z'));

$feed = new AtomGenerator\Feed();
$feed->setTitle('Blog');
$feed->setUpdatedDateTime(new DateTime('now'));
$feed->addEntry($entry);

assert(AtomGenerator\Feed::validate($feed->getDocument()));

echo $feed->saveXML();
```

## Validation

A validation tool is included with static method `Feed::validate`. It uses a [Relax NG](https://en.wikipedia.org/wiki/RELAX_NG) schema coming from <https://validator.w3.org/feed/docs/rfc4287.html#schema> (inspired by <https://cweiske.de/tagebuch/atom-validation.htm>).
