<?php
declare(strict_types=1);

namespace Prismic\Value;

use DateTimeImmutable;
use DateTimeInterface;
use Prismic\Document;
use Prismic\Document\Fragment;
use Prismic\Document\Fragment\Collection;
use Prismic\Document\Fragment\Factory;
use function array_map;
use function get_object_vars;

final class DocumentData implements Document
{
    use DataAssertionBehaviour;

    /** @var string */
    private $id;
    /** @var string */
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
    /** @var Collection */
    private $body;

    /**
     * @param string[] $tags
     * @param string[] $slugs
     */
    private function __construct(
        string $id,
        string $uid,
        string $type,
        string $lang,
        DateTimeImmutable $firstPublished,
        DateTimeImmutable $lastPublished,
        iterable $tags,
        iterable $slugs,
        Collection $body
    ) {
        $this->id = $id;
        $this->uid = $uid;
        $this->type = $type;
        $this->lang = $lang;
        $this->firstPublished = $firstPublished;
        $this->lastPublished = $lastPublished;
        $this->setTags(...$tags);
        $this->setSlugs(...$slugs);
        $this->body = $body;
    }

    public static function factory(object $data) : self
    {
        $documentBody = self::assertObjectPropertyIsObject($data, 'data');
        $body = Collection::new(array_map(static function ($value) : Fragment {
            return Factory::factory($value);
        }, get_object_vars($documentBody)));

        return new static(
            self::assertObjectPropertyIsString($data, 'id'),
            self::assertObjectPropertyIsString($data, 'uid'),
            self::assertObjectPropertyIsString($data, 'type'),
            self::assertObjectPropertyIsString($data, 'lang'),
            self::assertObjectPropertyIsUtcDateTime($data, 'first_publication_date'),
            self::assertObjectPropertyIsUtcDateTime($data, 'last_publication_date'),
            self::assertObjectPropertyIsArray($data, 'tags'),
            self::assertObjectPropertyIsArray($data, 'slugs'),
            $body
        );
    }

    public function id() : string
    {
        return $this->id;
    }

    public function uid() : string
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

    public function body() : Collection
    {
        return $this->body;
    }
}
