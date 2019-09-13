<?php

namespace App\Tests\Utils;

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

    /** @var bool reset file contents */
    protected static $reset = false;

    /**
     * @group buildXML
     */
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
        $entry->addLink('http://test.com/alternate_entry', 'alternate', 'text/html', 'en', 'alternate_entry');
        $entry->setContent('<em>Entry content</em> &amp; ...', 'html');
        $entry->setUpdatedDateTime(new DateTime('2019-05-04T21:00:40Z'));

        $feed->addEntry($entry);

        if (self::$reset) {
            file_put_contents(self::TEST_FEED_XML_PATH_1, $feed->saveXML()); // @codeCoverageIgnore
        }

        $valid = Feed::validateFeed($feed->getDocument(), $errors);
        self::assertTrue($valid, self::formatXmlErrors($errors));

        self::assertStringEqualsFile(self::TEST_FEED_XML_PATH_1, $feed->saveXML());
    }

    /**
     * @group buildXML
     */
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

        $valid = Feed::validateFeed($feed->getDocument(), $errors);
        self::assertTrue($valid, self::formatXmlErrors($errors));

        self::assertStringEqualsFile(self::TEST_FEED_XML_PATH_2, $feed->saveXML());
    }

    /**
     * @group buildXML
     */
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
        $entry->setUpdatedDateTime(new DateTime('2019-05-04T21:00:40Z'));

        $feed->addEntry($entry);

        if (self::$reset) {
            file_put_contents(self::TEST_FEED_XML_PATH_3, $feed->saveXML()); // @codeCoverageIgnore
        }

        $valid = Feed::validateFeed($feed->getDocument(), $errors);
        self::assertTrue($valid, self::formatXmlErrors($errors));

        self::assertStringEqualsFile(self::TEST_FEED_XML_PATH_3, $feed->saveXML());
    }

    /**
     * @group buildXML
     *
     * @codeCoverageIgnore
     */
    public function testFeedCreationException1(): void
    {
        $entry = new Entry();
        $this->expectException(InvalidArgumentException::class);
        $entry->setContent(null, 'text');
    }

    /**
     * @group buildXML
     *
     * @codeCoverageIgnore
     */
    public function testFeedCreationException2(): void
    {
        $entry = new Entry();
        $this->expectException(InvalidArgumentException::class);
        $entry->setContent(null, 'xhtml', 'xxx');
    }

    /**
     * @group buildXML
     *
     * @codeCoverageIgnore
     */
    public function testFeedCreationException3(): void
    {
        $entry = new Entry();
        $this->expectException(InvalidArgumentException::class);
        $entry->setId('xxx');
    }

    /**
     * @param null|libXMLError[] $errors
     *
     * @return string
     *
     * @codeCoverageIgnore
     */
    protected static function formatXmlErrors(?array $errors): string
    {
        $messages = [];
        foreach ($errors as $error) {
            $messages[] = trim($error->message);
        }

        return implode(PHP_EOL, $messages);
    }
}
