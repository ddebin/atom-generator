<?php

declare(strict_types = 1);

use AtomGenerator\Entry;
use AtomGenerator\Feed;

include_once 'vendor/autoload.php';

$entry = new Entry();
$entry->setTitle('Post', 'text');
$entry->setId('tag:id');
$entry->setContent('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'text');
$entry->setUpdatedDateTime(new DateTime('2019-05-04T21:00:40Z'));

$feed = new Feed();
$feed->setTitle('Blog');
$feed->setUpdatedDateTime(new DateTime('now'));
$feed->addEntry($entry);

assert(Feed::validate($feed->getDocument()));

echo $feed->saveXML();
