<?php

namespace AtomGenerator;

use DateTime;
use DateTimeInterface;
use DOMElement;
use InvalidArgumentException;
use SimpleXMLElement;
use Webmozart\Assert\Assert;

abstract class AbstractElement
{
    /** @var string */
    protected $id;

    /** @var string */
    protected $title;

    /** @var null|string */
    protected $titleType;

    /** @var DateTimeInterface */
    protected $updatedDateTime;

    /** @var null|string */
    protected $rights;

    /** @var null|string */
    protected $rightsType;

    /** @var string[][] */
    protected $authors = [];

    /** @var string[][] */
    protected $contributors = [];

    /** @var string[][] */
    protected $categories = [];

    /** @var mixed[][] */
    protected $links = [];

    /**
     * AbstractElement constructor.
     */
    public function __construct()
    {
        $this->setTitle('Example, Inc.');
        $this->setId('http://example.com/');
        $this->setUpdatedDateTime(new DateTime('now'));
    }

    public function setId(string $id): void
    {
        if ((0 !== strpos($id, 'tag:')) && (false === filter_var($id, FILTER_VALIDATE_URL))) {
            throw new InvalidArgumentException('Expected a value to be a valid URI/tag. Got '.$id);
        }
        $this->id = $id;
    }

    public function setTitle(string $title, ?string $type = null): void
    {
        Assert::oneOf($type, [null, 'text', 'xhtml', 'html']);
        $this->title = $title;
        $this->titleType = $type;
    }

    public function setUpdatedDateTime(DateTimeInterface $updated): void
    {
        $this->updatedDateTime = $updated;
    }

    public function setRights(?string $rights, ?string $type = null): void
    {
        Assert::true((null !== $rights) || (null === $type));
        Assert::oneOf($type, [null, 'text', 'xhtml', 'html']);
        $this->rights = $rights;
        $this->rightsType = $type;
    }

    public function addCategory(string $term, ?string $scheme = null, ?string $label = null): void
    {
        $category = [
            'term' => $term,
        ];

        if (null !== $scheme) {
            self::assertURL($scheme);
            $category['scheme'] = $scheme;
        }

        if (null !== $label) {
            $category['label'] = $label;
        }

        $this->categories[] = $category;
    }

    public function addLink(string $uri, ?string $rel = null, ?string $type = null, ?string $hreflang = null, ?string $title = null, ?int $length = null): void
    {
        self::assertURL($uri);

        $link = [
            'href' => $uri,
        ];

        if (null !== $rel) {
            Assert::oneOf($rel, [null, 'alternate', 'enclosure', 'related', 'self', 'via', 'payment']);
            $link['rel'] = $rel;
        }

        if (null !== $type) {
            $link['type'] = $type;
        }

        if (null !== $hreflang) {
            $link['hreflang'] = $hreflang;
        }

        if (null !== $title) {
            $link['title'] = $title;
        }

        if (null !== $length) {
            $link['length'] = $length;
        }

        $this->links[] = $link;
    }

    public function addAuthor(string $name, ?string $email = null, ?string $uri = null): void
    {
        $this->authors[] = self::createPerson($name, $email, $uri);
    }

    public function addContributor(string $name, ?string $email = null, ?string $uri = null): void
    {
        $this->contributors[] = self::createPerson($name, $email, $uri);
    }

    public function addChildrenTo(SimpleXMLElement $parent): void
    {
        $parent->addChild('id', htmlspecialchars($this->id));

        self::addChildWithTypeToElement($parent, 'title', $this->title, $this->titleType);

        $parent->addChild('updated', $this->updatedDateTime->format(DATE_ATOM));

        if (null !== $this->rights) {
            self::addChildWithTypeToElement($parent, 'rights', $this->rights, $this->rightsType);
        }

        foreach ([
            'category' => $this->categories,
            'link' => $this->links,
        ] as $type => $elements) {
            foreach ($elements as $element) {
                $child = $parent->addChild($type);
                foreach ($element as $name => $attribute) {
                    $child->addAttribute($name, $attribute);
                }
            }
        }

        foreach ([
            'author' => $this->authors,
            'contributor' => $this->contributors,
        ] as $type => $elements) {
            foreach ($elements as $element) {
                $child = $parent->addChild($type);
                foreach ($element as $name => $attribute) {
                    $child->addChild($name, htmlspecialchars($attribute));
                }
            }
        }
    }

    /**
     * @return mixed[]
     */
    protected static function createPerson(string $name, ?string $email = null, ?string $uri = null): array
    {
        $person = [
            'name' => $name,
        ];

        if (null !== $email) {
            Assert::email($email);
            $person['email'] = $email;
        }

        if (null !== $uri) {
            self::assertURL($uri);
            $person['uri'] = $uri;
        }

        return $person;
    }

    protected static function addChildWithTypeToElement(SimpleXMLElement $parent, string $name, ?string $data, ?string $type): SimpleXMLElement
    {
        if (null !== $data) {
            if (in_array($type, ['html', 'xhtml'], true)) {
                $element = $parent->addChild($name);
                self::addCData($data, $element);
            } else {
                $element = $parent->addChild($name, htmlspecialchars($data));
            }
        } else {
            $element = $parent->addChild($name);
        }
        if (null !== $type) {
            $element->addAttribute('type', $type);
        }

        return $element;
    }

    protected static function addCData(string $cdataText, SimpleXMLElement $element): void
    {
        $node = dom_import_simplexml($element);
        Assert::isInstanceOf($node, DOMElement::class);
        $no = $node->ownerDocument;
        assert(null !== $no);
        $node->appendChild($no->createCDATASection($cdataText));
    }

    protected static function assertURL(string $value): void
    {
        if (false === filter_var($value, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Expected a value to be a valid URI. Got '.$value);
        }
    }
}
