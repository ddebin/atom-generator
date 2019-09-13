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
    protected $updateDateTime;

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

    /** @var string[][] */
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

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        if ((0 !== strpos($id, 'tag:')) && (false === filter_var($id, FILTER_VALIDATE_URL))) {
            throw new InvalidArgumentException('Expected a value to be a valid URI/tag. Got '.$id);
        }
        $this->id = $id;
    }

    /**
     * @param string      $title
     * @param null|string $type
     */
    public function setTitle(string $title, ?string $type = null): void
    {
        Assert::oneOf($type, [null, 'text', 'xhtml', 'html']);
        $this->title = $title;
        $this->titleType = $type;
    }

    /**
     * @param DateTimeInterface $updated
     */
    public function setUpdatedDateTime(DateTimeInterface $updated): void
    {
        $this->updateDateTime = $updated;
    }

    /**
     * @param null|string $rights
     * @param null|string $type
     */
    public function setRights(?string $rights, ?string $type = null): void
    {
        Assert::true((null !== $rights) || (null === $type));
        Assert::oneOf($type, [null, 'text', 'xhtml', 'html']);
        $this->rights = $rights;
        $this->rightsType = $type;
    }

    /**
     * @param string      $term
     * @param null|string $scheme
     * @param null|string $label
     */
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

    /**
     * @param string      $url
     * @param null|string $rel
     * @param null|string $type
     * @param null|string $hreflang
     * @param null|string $title
     */
    public function addLink(string $url, ?string $rel = null, ?string $type = null, ?string $hreflang = null, ?string $title = null): void
    {
        self::assertURL($url);

        $link = [
            'href' => $url,
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

        $this->links[] = $link;
    }

    /**
     * @param string      $name
     * @param null|string $email
     * @param null|string $uri
     */
    public function addAuthor(string $name, ?string $email = null, ?string $uri = null): void
    {
        $this->authors[] = self::createPerson($name, $email, $uri);
    }

    /**
     * @param string      $name
     * @param null|string $email
     * @param null|string $uri
     */
    public function addContributor(string $name, ?string $email = null, ?string $uri = null): void
    {
        $this->contributors[] = self::createPerson($name, $email, $uri);
    }

    /**
     * @param SimpleXMLElement $parent
     */
    public function addChildrenTo(SimpleXMLElement $parent): void
    {
        $parent->addChild('id', $this->id);

        self::addChildWithTypeToElement($parent, 'title', $this->title, $this->titleType);

        $parent->addChild('updated', $this->updateDateTime->format(DATE_ATOM));

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
                    $child->addChild($name, $attribute);
                }
            }
        }
    }

    /**
     * @param string      $name
     * @param null|string $email
     * @param null|string $uri
     *
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

    /**
     * @param SimpleXMLElement $parent
     * @param string           $name
     * @param null|string      $data
     * @param null|string      $type
     *
     * @return SimpleXMLElement
     */
    protected static function addChildWithTypeToElement(SimpleXMLElement $parent, string $name, ?string $data, ?string $type): SimpleXMLElement
    {
        if (null !== $data) {
            if (in_array($type, ['html', 'xhtml'], true)) {
                $element = $parent->addChild($name);
                self::addCData($data, $element);
            } else {
                $element = $parent->addChild($name, $data);
            }
        } else {
            $element = $parent->addChild($name);
        }
        if (null !== $type) {
            $element->addAttribute('type', $type);
        }

        return $element;
    }

    /**
     * @param string           $cdataText
     * @param SimpleXMLElement $element
     */
    protected static function addCData(string $cdataText, SimpleXMLElement $element): void
    {
        $node = dom_import_simplexml($element);
        Assert::isInstanceOf($node, DOMElement::class);
        $no = $node->ownerDocument;
        $node->appendChild($no->createCDATASection($cdataText));
    }

    /**
     * @param string $value
     */
    protected static function assertURL(string $value): void
    {
        if (false === filter_var($value, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Expected a value to be a valid URI. Got '.$value);
        }
    }
}
