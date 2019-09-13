<?php

namespace AtomGenerator;

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

    /**
     * @param null|string $summary
     * @param null|string $type
     */
    public function setSummary(?string $summary, ?string $type = null): void
    {
        Assert::true((null !== $summary) || (null === $type));
        Assert::oneOf($type, [null, 'text', 'xhtml', 'html']);
        $this->summary = $summary;
        $this->summaryType = $type;
    }

    /**
     * @param null|string $content
     * @param null|string $type
     * @param null|string $src
     */
    public function setContent(?string $content, ?string $type = null, ?string $src = null): void
    {
        Assert::true(((null !== $content) && (null === $src)) ||
            ((null === $content) && (null !== $src)) ||
            ((null === $content) && (null === $src) && (null === $type)));
        Assert::oneOf($type, [null, 'text', 'xhtml', 'html']);
        if (null !== $src) {
            self::assertURL($src);
        }
        $this->content = $content;
        $this->contentType = $type;
        $this->contentSrc = $src;
    }

    /**
     * @param SimpleXMLElement $parent
     */
    public function addChildrenTo(SimpleXMLElement $parent): void
    {
        $entry = $parent->addChild('entry');

        parent::addChildrenTo($entry);

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
    }
}
