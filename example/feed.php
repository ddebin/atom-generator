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
