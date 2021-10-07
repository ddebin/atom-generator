<?php

namespace AtomGenerator;

use DateTimeInterface;
use SimpleXMLElement;
use Webmozart\Assert\Assert;

class Entry extends AbstractElement
{
    /** @var null|string */
    protected $summary;

    /** @var null|string */
    protected $summaryType;

    /** @var null|string */
    protected $content;

    /** @var null|string */
    protected $contentType;

    /** @var null|string */
    protected $contentSrc;

    /** @var null|DateTimeInterface */
    protected $publishedDateTime;

    /** @var null|Feed */
    protected $source;

    public function setSummary(?string $summary, ?string $type = null): void
    {
        Assert::true((null !== $summary) || (null === $type));
        Assert::oneOf($type, [null, 'text', 'xhtml', 'html']);
        $this->summary = $summary;
        $this->summaryType = $type;
    }

    public function setContent(?string $content, ?string $type = null, ?string $src = null): void
    {
        Assert::true(((null !== $content) && (null === $src))
            || ((null === $content) && (null !== $src))
            || ((null === $content) && (null === $src) && (null === $type)));
        if (null !== $src) {
            self::assertURL($src);
        }
        $this->content = $content;
        $this->contentType = $type;
        $this->contentSrc = $src;
    }

    public function setPublishedDateTime(?DateTimeInterface $publishedDateTime): void
    {
        $this->publishedDateTime = $publishedDateTime;
    }

    public function setSource(?Feed $sourceFeed): void
    {
        if (null !== $sourceFeed) {
            Assert::count($sourceFeed->getEntries(), 0, 'Source feed must not contain entries.');
        }
        $this->source = $sourceFeed;
    }

    public function addChildrenTo(SimpleXMLElement $parent): void
    {
        if ((null === $this->content) && (null === $this->contentSrc)) {
            $valid = false;
            foreach ($this->links as $link) {
                if ('alternate' === $link['rel']) {
                    $valid = true;
                }
            }
            Assert::true($valid, 'Content must be provided if there is no alternate link.');
        }

        $entry = $parent->addChild('entry');

        parent::addChildrenTo($entry);

        if (null !== $this->publishedDateTime) {
            $entry->addChild('published', $this->publishedDateTime->format(DATE_ATOM));
        }
        if (null !== $this->summary) {
            self::addChildWithTypeToElement($entry, 'summary', $this->summary, $this->summaryType);
        }
        if (null !== $this->content) {
            self::addChildWithTypeToElement($entry, 'content', $this->content, $this->contentType);
        }
        if (null !== $this->contentSrc) {
            $content = self::addChildWithTypeToElement($entry, 'content', null, $this->contentType);
            $content->addAttribute('src', $this->contentSrc);
        }
        if (null !== $this->source) {
            $source = $entry->addChild('source');
            $this->source->addChildrenTo($source);
        }
    }
}
