![Travis (.org)](https://img.shields.io/travis/ddebin/atom-generator?logo=travis&style=for-the-badge)
![Codecov](https://img.shields.io/codecov/c/github/ddebin/atom-generator?logo=codecov&style=for-the-badge)
![PHP from Packagist](https://img.shields.io/packagist/php-v/ddebin/atom-generator?logo=php&style=for-the-badge)
![Packagist Version](https://img.shields.io/packagist/v/ddebin/atom-generator?style=for-the-badge)
![Packagist](https://img.shields.io/packagist/l/ddebin/atom-generator?style=for-the-badge)

# Atom feed generator

This library is an [Atom](https://en.wikipedia.org/wiki/Atom_(Web_standard) feed generator, PHP 7.1+, fully typed ([PHPStan](https://github.com/phpstan/phpstan) level 7, 100% code coverage). Follows W3C standard
([RFC 4287](https://validator.w3.org/feed/docs/rfc4287.html)).

## Installing

To include `mc-google-visualization` in your project, add it to your `composer.json` file:

```json
{
    "require": {
        "ddebin/atom-generator": "^0.1"
    }
}
```

## Example

```php
<?php

include_once 'vendor/autoload.php';

$feed = new AtomGenerator\Feed();
$feed->setTitle('Blog');
$feed->setUpdatedDateTime(new DateTime('now'));

$entry = new AtomGenerator\Entry();
$entry->setTitle('Post', 'text');
$entry->setId('tag:id');
$entry->setContent('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'text');
$entry->setUpdatedDateTime(new DateTime('2019-05-04T21:00:40Z'));

$feed->addEntry($entry);

echo $feed->saveXML();
```

## Validation

A validation tool is included with static method `Feed::validate`. It uses a [Relax NG](https://en.wikipedia.org/wiki/RELAX_NG) schema coming from <https://validator.w3.org/feed/docs/rfc4287.html#schema> (inspired by <https://cweiske.de/tagebuch/atom-validation.htm>).
