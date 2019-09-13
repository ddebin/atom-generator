<?php

namespace AtomGenerator;

use DOMDocument;
use DOMElement;
use LibXMLError;
use SimpleXMLElement;
use Webmozart\Assert\Assert;

class Feed extends AbstractElement
{
    /** @var Entry[] */
    protected $entries = [];

    /** @var bool */
    protected $prettify = false;

    /** @var null|string */
    protected $language;

    /** @var null|string */
    protected $subtitle;

    /** @var null|string */
    protected $icon;

    /** @var null|string */
    protected $logo;

    /** @var null|string */
    protected $generator;

    /** @var null|string */
    protected $generatorVersion;

    /** @var null|string */
    protected $generatorUri;

    /** @var mixed[][] */
    protected $customElements = [];

    /**
     * Feed constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setPrettify(true);
    }

    /**
     * @param bool $prettify
     */
    public function setPrettify(bool $prettify): void
    {
        $this->prettify = $prettify;
    }

    /**
     * @param null|string $language
     */
    public function setLanguage(?string $language): void
    {
        $this->language = $language;
    }

    /**
     * @param null|string $subtitle
     */
    public function setSubtitle(?string $subtitle): void
    {
        $this->subtitle = $subtitle;
    }

    /**
     * @param null|string $uri
     */
    public function setIconUri(?string $uri): void
    {
        if (null !== $uri) {
            self::assertURL($uri);
        }
        $this->icon = $uri;
    }

    /**
     * @param null|string $uri
     */
    public function setLogoUri(?string $uri): void
    {
        if (null !== $uri) {
            self::assertURL($uri);
        }
        $this->logo = $uri;
    }

    /**
     * @param null|string $generator
     * @param null|string $uri
     * @param null|string $version
     */
    public function setGenerator(?string $generator, ?string $uri = null, ?string $version = null): void
    {
        Assert::true((null !== $generator) || ((null === $uri) && (null === $version)));

        $this->generator = $generator;
        $this->generatorVersion = $version;

        if (null !== $uri) {
            self::assertURL($uri);
            $this->generatorUri = $uri;
        }
    }

    /**
     * @param Entry $entry
     */
    public function addEntry(Entry $entry): void
    {
        $this->entries[] = $entry;
    }

    /**
     * @param string        $ns
     * @param string        $url
     * @param string        $name
     * @param mixed         $value
     * @param null|string[] $attributes
     */
    public function addCustomElement(string $ns, string $url, string $name, $value, ?array $attributes = null): void
    {
        self::assertURL($url);

        $this->customElements[] = [
            'ns' => $ns,
            'url' => $url,
            'name' => $name,
            'value' => $value,
            'attributes' => $attributes ?? [],
        ];
    }

    /**
     * @param SimpleXMLElement $parent
     */
    public function addChildrenTo(SimpleXMLElement $parent): void
    {
        parent::addChildrenTo($parent);

        if (null !== $this->subtitle) {
            $parent->addChild('subtitle', $this->subtitle);
        }

        if (null !== $this->logo) {
            $parent->addChild('logo', $this->logo);
        }

        if (null !== $this->icon) {
            $parent->addChild('icon', $this->icon);
        }

        if (null !== $this->generator) {
            $generator = $parent->addChild('generator', $this->generator);
            if (null !== $this->generatorVersion) {
                $generator->addAttribute('version', $this->generatorVersion);
            }
            if (null !== $this->generatorUri) {
                $generator->addAttribute('uri', $this->generatorUri);
            }
        }

        foreach ($this->customElements as $customElement) {
            $element = $parent->addChild($customElement['name'], $customElement['value'], $customElement['ns']);
            foreach ($customElement['attributes'] as $name => $value) {
                $element->addAttribute($name, $value);
            }
        }

        foreach ($this->entries as $entry) {
            $entry->addChildrenTo($parent);
        }
    }

    /**
     * @return SimpleXMLElement
     */
    public function getSimpleXML(): SimpleXMLElement
    {
        $attributes = [];
        if (null !== $this->language) {
            $attributes['xml:lang'] = $this->language;
        }
        foreach ($this->customElements as $customElement) {
            $attributes['xmlns:'.$customElement['ns']] = $customElement['url'];
        }

        $attributesString = '';
        foreach ($attributes as $name => $attribute) {
            $attributesString .= " {$name}=\"{$attribute}\"";
        }

        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><feed xmlns="http://www.w3.org/2005/Atom"'.$attributesString.' />');

        $this->addChildrenTo($xml);

        return $xml;
    }

    /**
     * @return DOMDocument
     */
    public function getDocument(): DOMDocument
    {
        $node = dom_import_simplexml($this->getSimpleXML());
        Assert::isInstanceOf($node, DOMElement::class);

        return $node->ownerDocument;
    }

    /**
     * @return string
     */
    public function saveXML(): string
    {
        $dom = $this->getDocument();
        $dom->preserveWhiteSpace = !$this->prettify;
        $dom->formatOutput = $this->prettify;

        return $dom->saveXML();
    }

    /**
     * Validate XML DOMDocument against ATOM NRG schema.
     * NRG schema coming from https://validator.w3.org/feed/docs/rfc4287.html#schema.
     *
     * @see https://cweiske.de/tagebuch/atom-validation.htm
     *
     * @param DOMDocument        $document
     * @param null|libXMLError[] $errors
     *
     * @return bool
     */
    public static function validateFeed(DOMDocument $document, ?array &$errors = null): bool
    {
        libxml_use_internal_errors(true);
        $valid = $document->relaxNGValidate(__DIR__.'/atom.rng');
        $errors = libxml_get_errors();
        libxml_clear_errors();

        return $valid;
    }
}
