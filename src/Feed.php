<?php

namespace AtomGenerator;

use DOMDocument;
use Exception;
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

    /**
     * @var array[]
     *
     * @phpstan-var array<array{ns: string, uri: string, name: string, value: string, attributes: string[]}>
     */
    protected $customElements = [];

    /**
     * Feed constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setPrettify(true);
    }

    public function setPrettify(bool $prettify): void
    {
        $this->prettify = $prettify;
    }

    public function setLanguage(?string $language): void
    {
        $this->language = $language;
    }

    public function setSubtitle(?string $subtitle): void
    {
        $this->subtitle = $subtitle;
    }

    public function setIconUri(?string $uri): void
    {
        if (null !== $uri) {
            self::assertURL($uri);
        }
        $this->icon = $uri;
    }

    public function setLogoUri(?string $uri): void
    {
        if (null !== $uri) {
            self::assertURL($uri);
        }
        $this->logo = $uri;
    }

    public function setGenerator(?string $generator, ?string $uri = null, ?string $version = null): void
    {
        Assert::true((null !== $generator) || ((null === $uri) && (null === $version)));
        if (null !== $uri) {
            self::assertURL($uri);
        }
        $this->generator = $generator;
        $this->generatorVersion = $version;
        $this->generatorUri = $uri;
    }

    public function addEntry(Entry $entry): void
    {
        $this->entries[] = $entry;
    }

    /**
     * @param Entry[] $entries
     */
    public function setEntries(array $entries): void
    {
        $this->entries = $entries;
    }

    /**
     * @return Entry[]
     */
    public function getEntries(): array
    {
        return $this->entries;
    }

    /**
     * @param null|string[] $attributes
     */
    public function addCustomElement(string $ns, string $uri, string $name, string $value, ?array $attributes = null): void
    {
        self::assertURL($uri);

        $this->customElements[] = [
            'ns' => $ns,
            'uri' => $uri,
            'name' => $name,
            'value' => $value,
            'attributes' => $attributes ?? [],
        ];
    }

    public function addChildrenTo(SimpleXMLElement $parent): void
    {
        parent::addChildrenTo($parent);

        if (null !== $this->subtitle) {
            $parent->addChild('subtitle', htmlspecialchars($this->subtitle));
        }

        if (null !== $this->logo) {
            $parent->addChild('logo', htmlspecialchars($this->logo));
        }

        if (null !== $this->icon) {
            $parent->addChild('icon', htmlspecialchars($this->icon));
        }

        if (null !== $this->generator) {
            $generator = $parent->addChild('generator', htmlspecialchars($this->generator));
            if (null !== $this->generatorVersion) {
                $generator->addAttribute('version', $this->generatorVersion);
            }
            if (null !== $this->generatorUri) {
                $generator->addAttribute('uri', $this->generatorUri);
            }
        }

        foreach ($this->customElements as $customElement) {
            $element = $parent->addChild($customElement['name'], htmlspecialchars($customElement['value']), $customElement['uri']);
            foreach ($customElement['attributes'] as $name => $value) {
                $element->addAttribute($name, $value);
            }
        }

        foreach ($this->entries as $entry) {
            $entry->addChildrenTo($parent);
        }
    }

    /**
     * @throws Exception
     */
    public function getSimpleXML(): SimpleXMLElement
    {
        $attributes = [];
        if (null !== $this->language) {
            $attributes['xml:lang'] = $this->language;
        }
        foreach ($this->customElements as $customElement) {
            $attributes['xmlns:'.$customElement['ns']] = $customElement['uri'];
        }

        $attributesString = '';
        foreach ($attributes as $name => $attribute) {
            $attributesString .= ' '.$name.'="'.htmlspecialchars($attribute).'"';
        }

        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><feed xmlns="http://www.w3.org/2005/Atom"'.$attributesString.' />');

        $this->addChildrenTo($xml);

        return $xml;
    }

    /**
     * @throws Exception
     */
    public function getDocument(): DOMDocument
    {
        $node = dom_import_simplexml($this->getSimpleXML());
        $no = $node->ownerDocument;
        assert(null !== $no);

        return $no;
    }

    /**
     * @return false|string
     *
     * @throws Exception
     */
    public function saveXML()
    {
        $dom = $this->getDocument();
        $dom->preserveWhiteSpace = !$this->prettify;
        $dom->formatOutput = $this->prettify;

        return $dom->saveXML();
    }

    /**
     * Validate XML DOMDocument against an ATOM Relax NG schema.
     * RNG schema coming from https://validator.w3.org/feed/docs/rfc4287.html#schema.
     *
     * @see https://cweiske.de/tagebuch/atom-validation.htm
     *
     * @param null|libXMLError[] $errors
     */
    public static function validate(DOMDocument $document, ?array &$errors = null): bool
    {
        libxml_use_internal_errors(true);
        $valid = $document->relaxNGValidate(__DIR__.'/atom.rng');
        $errors = libxml_get_errors();
        libxml_clear_errors();

        return $valid;
    }
}
