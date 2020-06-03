<?php
declare(strict_types=1);

namespace Prismic\Value;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Prismic\Document;
use Prismic\Document\Fragment;
use Prismic\Document\Fragment\Collection;
use Prismic\Document\Fragment\DocumentLink;
use Prismic\Document\Fragment\Factory;
use Prismic\Document\FragmentCollection;
use function array_map;
use function get_object_vars;
use function reset;

final class DocumentData implements Document
{
    use DataAssertionBehaviour;

    /** @var string */
    private $id;
    /** @var string|null */
    private $uid;
    /** @var string */
    private $type;
    /** @var string */
    private $lang;
    /** @var DateTimeImmutable */
    private $firstPublished;
    /** @var DateTimeImmutable */
    private $lastPublished;
    /** @var string[] */
    private $tags;
    /** @var string[] */
    private $slugs;
    /** @var FragmentCollection */
    private $body;
    /** @var Translation[] */
    private $translations;

    /**
     * @param string[]      $tags
     * @param string[]      $slugs
     * @param Translation[] $translations
     */
    private function __construct(
        string $id,
        ?string $uid,
        string $type,
        string $lang,
        DateTimeImmutable $firstPublished,
        DateTimeImmutable $lastPublished,
        iterable $tags,
        iterable $slugs,
        iterable $translations,
        FragmentCollection $body
    ) {
        $this->id = $id;
        $this->uid = $uid;
        $this->type = $type;
        $this->lang = $lang;
        $this->firstPublished = $firstPublished;
        $this->lastPublished = $lastPublished;
        $this->setTags(...$tags);
        $this->setSlugs(...$slugs);
        $this->setTranslations(...$translations);
        $this->body = $body;
    }

    public static function factory(object $data) : self
    {
        $documentBody = self::assertObjectPropertyIsObject($data, 'data');
        $body = Collection::new(array_map(static function ($value) : Fragment {
            return Factory::factory($value);
        }, get_object_vars($documentBody)));

        $translations = array_map(static function (object $value) : Translation {
            return Translation::factory($value);
        }, self::assertObjectPropertyIsArray($data, 'alternate_languages'));

        /**
         * In Preview mode, Document dates are nullified, FFS.
         */
        foreach (['first_publication_date', 'last_publication_date'] as $prop) {
            if (isset($data->{$prop}) && $data->{$prop} !== null) {
                continue;
            }

            $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
            $data->{$prop} = $now->format(DateTimeImmutable::ATOM);
        }

        return new static(
            self::assertObjectPropertyIsString($data, 'id'),
            self::optionalStringProperty($data, 'uid'),
            self::assertObjectPropertyIsString($data, 'type'),
            self::assertObjectPropertyIsString($data, 'lang'),
            self::assertObjectPropertyIsUtcDateTime($data, 'first_publication_date'),
            self::assertObjectPropertyIsUtcDateTime($data, 'last_publication_date'),
            self::assertObjectPropertyIsArray($data, 'tags'),
            self::assertObjectPropertyIsArray($data, 'slugs'),
            $translations,
            $body
        );
    }

    public function id() : string
    {
        return $this->id;
    }

    public function uid() :? string
    {
        return $this->uid;
    }

    public function type() : string
    {
        return $this->type;
    }

    /** @inheritDoc */
    public function tags() : iterable
    {
        return $this->tags;
    }

    /** @inheritDoc */
    public function slugs() : iterable
    {
        return $this->slugs;
    }

    public function slug() : string
    {
        return reset($this->slugs);
    }

    public function lang() : string
    {
        return $this->lang;
    }

    public function firstPublished() : DateTimeInterface
    {
        return $this->firstPublished;
    }

    public function lastPublished() : DateTimeInterface
    {
        return $this->lastPublished;
    }

    private function setTags(string ...$tags) : void
    {
        $this->tags = $tags;
    }

    private function setSlugs(string ...$slugs) : void
    {
        $this->slugs = $slugs;
    }

    public function content() : FragmentCollection
    {
        return $this->body;
    }

    private function setTranslations(Translation ...$translations) : void
    {
        $this->translations = $translations;
    }

    /** @return Translation[] */
    public function translations() : iterable
    {
        return $this->translations;
    }

    public function asLink() : DocumentLink
    {
        return DocumentLink::withDocument($this);
    }

    public function data() : DocumentData
    {
        return $this;
    }
}
