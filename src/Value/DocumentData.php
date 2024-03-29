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

final class DocumentData implements Document
{
    use DataAssertionBehaviour;

    /** @var string[] */
    private array $tags;
    /** @var Translation[] */
    private array $translations;

    /**
     * @param string[]      $tags
     * @param Translation[] $translations
     */
    private function __construct(
        private string $id,
        private string|null $uid,
        private string $type,
        private string $lang,
        private DateTimeImmutable $firstPublished,
        private DateTimeImmutable $lastPublished,
        iterable $tags,
        iterable $translations,
        private FragmentCollection $body,
    ) {
        $this->setTags(...$tags);
        $this->setTranslations(...$translations);
    }

    public static function factory(object $data): self
    {
        $documentBody = self::assertObjectPropertyIsObject($data, 'data');
        $body = Collection::new(array_map(static function ($value): Fragment {
            return Factory::factory($value);
        }, get_object_vars($documentBody)));

        $translations = array_map(static function (object $value): Translation {
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
            $data->{$prop} = $now->format(DateTimeInterface::ATOM);
        }

        return new self(
            self::assertObjectPropertyIsString($data, 'id'),
            self::optionalStringProperty($data, 'uid'),
            self::assertObjectPropertyIsString($data, 'type'),
            self::assertObjectPropertyIsString($data, 'lang'),
            self::assertObjectPropertyIsUtcDateTime($data, 'first_publication_date'),
            self::assertObjectPropertyIsUtcDateTime($data, 'last_publication_date'),
            self::assertObjectPropertyAllString($data, 'tags'),
            $translations,
            $body,
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function uid(): string|null
    {
        return $this->uid;
    }

    public function type(): string
    {
        return $this->type;
    }

    /** @inheritDoc */
    public function tags(): iterable
    {
        return $this->tags;
    }

    public function lang(): string
    {
        return $this->lang;
    }

    public function firstPublished(): DateTimeInterface
    {
        return $this->firstPublished;
    }

    public function lastPublished(): DateTimeInterface
    {
        return $this->lastPublished;
    }

    private function setTags(string ...$tags): void
    {
        $this->tags = $tags;
    }

    public function content(): FragmentCollection
    {
        return $this->body;
    }

    private function setTranslations(Translation ...$translations): void
    {
        $this->translations = $translations;
    }

    /** @return Translation[] */
    public function translations(): iterable
    {
        return $this->translations;
    }

    public function asLink(): DocumentLink
    {
        return DocumentLink::withDocument($this);
    }

    public function data(): DocumentData
    {
        return $this;
    }
}
