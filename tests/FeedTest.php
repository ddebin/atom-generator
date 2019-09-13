<?php

namespace Tests;

use AtomGenerator\Entry;
use AtomGenerator\Feed;
use DateTime;
use InvalidArgumentException;
use LibXMLError;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class FeedTest extends TestCase
{
    protected const TEST_FEED_XML_PATH_1 = __DIR__.'/feed_1.xml';
    protected const TEST_FEED_XML_PATH_2 = __DIR__.'/feed_2.xml';
    protected const TEST_FEED_XML_PATH_3 = __DIR__.'/feed_3.xml';
    protected const TEST_FEED_XML_PATH_4 = __DIR__.'/feed_4.xml';

    /** @var bool reset file contents */
    protected static $reset = false;

    public function testFeedCreation1(): void
    {
        $feed = new Feed();
        $feed->setTitle('title');
        $feed->addAuthor('author', 'author@test.com', 'http://test.com/author');
        $feed->addAuthor('author', 'author@test.com', 'http://test.com/author');
        $feed->setRights('©'.date('Y'));
        $feed->addLink('http://test.com/link', 'via', 'text/html');
        $feed->addCategory('term', 'http://scheme.com', 'label');
        $feed->setId('tag:test');
        $feed->setLanguage('en');
        $feed->setIconUri('http://test.com/icon');
        $feed->setLogoUri('http://test.com/logo');
        $feed->setSubtitle('subtitle');
        $feed->setGenerator('generator', 'http://test.com/generator', 'version');
        $feed->addContributor('contributor', 'contributor@test.com', 'http://test.com/contributor');
        $feed->setUpdatedDateTime(new DateTime('2019-05-04T20:00:40Z'));
        $feed->addCustomElement('sy', 'http://purl.org/rss/1.0/modules/syndication', 'updatePeriod', 'hourly');
        $feed->addCustomElement('sy', 'http://purl.org/rss/1.0/modules/syndication', 'sy:updateFrequency', 10);

        $entry = new Entry();
        $entry->setTitle('entry title', 'text');
        $entry->setSummary('entry summary', 'text');
        $entry->setSummary('entry summary', 'text');
        $entry->setId('tag:entry-test');
        $entry->setRights('©'.date('Y'));
        $entry->addAuthor('author', 'test@test.com', 'http://test.com/author');
        $entry->addCategory('term', 'ftp://scheme.org', 'label');
        $entry->addContributor('contributor', 'contributor@test.com', 'http://test.com/contributor');
        $entry->addLink('http://test.com/alternate_entry', 'alternate', 'text/html', 'en', 'alternate_entry', 300);
        $entry->setContent('<em>Entry content</em> &amp; ...', 'html');
        $entry->setUpdatedDateTime(new DateTime('2019-05-04T21:00:40Z'));
        $entry->setPublishedDateTime(new DateTime('2019-04-04T21:00:40Z'));

        $feed->addEntry($entry);

        self::assertSame([$entry], $feed->getEntries());

        if (self::$reset) {
            file_put_contents(self::TEST_FEED_XML_PATH_1, $feed->saveXML()); // @codeCoverageIgnore
        }

        $valid = Feed::validate($feed->getDocument(), $errors);
        self::assertTrue($valid, self::formatXmlErrors($errors));

        self::assertXmlStringEqualsXmlFile(self::TEST_FEED_XML_PATH_1, $feed->saveXML());
    }

    public function testFeedCreation2(): void
    {
        $feed = new Feed();
        $feed->setPrettify(false);
        $feed->setTitle('title');
        $feed->setId('tag:test');
        $feed->setGenerator(null);
        $feed->setUpdatedDateTime(new DateTime('2019-05-04T20:00:40Z'));

        $entry = new Entry();
        $entry->setTitle('entry title', 'html');
        $entry->setSummary('entry summary', 'text');
        $entry->setSummary(null);
        $entry->setId('tag:entry-test');
        $entry->setRights(null);
        $entry->setContent(null, null, 'http://test.com/content');
        $entry->setUpdatedDateTime(new DateTime('2019-05-04T21:00:40Z'));

        $feed->addEntry($entry);

        if (self::$reset) {
            file_put_contents(self::TEST_FEED_XML_PATH_2, $feed->saveXML()); // @codeCoverageIgnore
        }

        $valid = Feed::validate($feed->getDocument(), $errors);
        self::assertTrue($valid, self::formatXmlErrors($errors));

        self::assertXmlStringEqualsXmlFile(self::TEST_FEED_XML_PATH_2, $feed->saveXML());
    }

    public function testFeedCreation3(): void
    {
        $feed = new Feed();
        $feed->setTitle('title');
        $feed->setId('tag:test');
        $feed->setUpdatedDateTime(new DateTime('2019-05-04T20:00:40Z'));

        $entry = new Entry();
        $entry->setTitle('entry title', 'html');
        $entry->setId('tag:entry-test');
        $entry->setContent(null);
        $entry->addLink('http://alternate.com', 'alternate');
        $entry->setUpdatedDateTime(new DateTime('2019-05-04T21:00:40Z'));

        $feed->addEntry($entry);

        if (self::$reset) {
            file_put_contents(self::TEST_FEED_XML_PATH_3, $feed->saveXML()); // @codeCoverageIgnore
        }

        $valid = Feed::validate($feed->getDocument(), $errors);
        self::assertTrue($valid, self::formatXmlErrors($errors));

        self::assertXmlStringEqualsXmlFile(self::TEST_FEED_XML_PATH_3, $feed->saveXML());
    }

    public function testFeedCreation4(): void
    {
        $sourceFeed = new Feed();
        $sourceFeed->setTitle('source title');
        $sourceFeed->setId('tag:source');
        $sourceFeed->setUpdatedDateTime(new DateTime('2019-03-04T20:00:40Z'));

        $feed = new Feed();
        $feed->setTitle('title');
        $feed->setId('tag:test');
        $feed->setUpdatedDateTime(new DateTime('2019-05-04T20:00:40Z'));

        $entry = new Entry();
        $entry->setTitle('entry title', 'html');
        $entry->setId('tag:entry-test');
        $entry->setContent(null);
        $entry->addLink('http://alternate.com', 'alternate');
        $entry->setUpdatedDateTime(new DateTime('2019-05-04T21:00:40Z'));
        $entry->setSource($sourceFeed);

        $feed->addEntry($entry);

        if (self::$reset) {
            file_put_contents(self::TEST_FEED_XML_PATH_4, $feed->saveXML()); // @codeCoverageIgnore
        }

        $valid = Feed::validate($feed->getDocument(), $errors);
        self::assertTrue($valid, self::formatXmlErrors($errors));

        self::assertXmlStringEqualsXmlFile(self::TEST_FEED_XML_PATH_4, $feed->saveXML());
    }

    /**
     * @codeCoverageIgnore
     */
    public function testFeedCreationException1(): void
    {
        $entry = new Entry();
        $this->expectException(InvalidArgumentException::class);
        $entry->setContent(null, 'text');
    }

    /**
     * @codeCoverageIgnore
     */
    public function testFeedCreationException2(): void
    {
        $entry = new Entry();
        $this->expectException(InvalidArgumentException::class);
        $entry->setContent(null, 'xhtml', 'xxx');
    }

    /**
     * @codeCoverageIgnore
     */
    public function testFeedCreationException3(): void
    {
        $entry = new Entry();
        $this->expectException(InvalidArgumentException::class);
        $entry->setId('xxx');
    }

    /**
     * @codeCoverageIgnore
     */
    public function testFeedCreationException4(): void
    {
        $feed = new Feed();
        $feed->setTitle('title');
        $feed->setId('tag:test');
        $feed->setUpdatedDateTime(new DateTime('2019-05-04T20:00:40Z'));

        $entry = new Entry();
        $entry->setTitle('entry title', 'html');
        $entry->setId('tag:entry-test');
        $entry->setUpdatedDateTime(new DateTime('2019-05-04T21:00:40Z'));

        $feed->setEntries([$entry]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Content must be provided if there is no alternate link.');
        $feed->saveXML();
    }

    /**
     * @param libXMLError[] $errors
     *
     * @return string
     *
     * @codeCoverageIgnore
     */
    protected static function formatXmlErrors(array $errors): string
    {
        $messages = [];
        foreach ($errors as $error) {
            $messages[] = trim($error->message);
        }

        return implode(PHP_EOL, $messages);
    }
}
