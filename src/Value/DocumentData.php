<?php

declare(strict_types=1);

namespace Prismic\Value;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Override;
use Prismic\Document;
use Prismic\Document\Fragment;
use Prismic\Document\Fragment\Collection;
use Prismic\Document\Fragment\DocumentLink;
use Prismic\Document\Fragment\Factory;
use Prismic\Document\FragmentCollection;

use function array_map;
use function array_values;
use function get_object_vars;

final readonly class DocumentData implements Document
{
    use DataAssertionBehaviour;

    /**
     * @param non-empty-string       $id
     * @param non-empty-string|null  $uid
     * @param non-empty-string       $type
     * @param non-empty-string       $lang
     * @param list<non-empty-string> $tags
     * @param list<Translation>      $translations
     */
    private function __construct(
        private string $id,
        private string|null $uid,
        private string $type,
        private string $lang,
        private DateTimeImmutable $firstPublished,
        private DateTimeImmutable $lastPublished,
        private string|null $url,
        private array $tags,
        private array $translations,
        private FragmentCollection $body,
    ) {
    }

    public static function factory(object $data): self
    {
        $documentBody = self::assertObjectPropertyIsObject($data, 'data');
        $body = Collection::new(array_map(static function ($value): Fragment {
            return Factory::factory($value);
        }, get_object_vars($documentBody)));

        $translations = array_values(array_map(static function (object $value): Translation {
            return Translation::factory($value);
        }, self::assertObjectPropertyIsArray($data, 'alternate_languages')));

        /**
         * In Preview mode, Document dates are nullified, FFS.
         */
        foreach (['first_publication_date', 'last_publication_date'] as $prop) {
            if (isset($data->{$prop})) {
                continue;
            }

            $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
            $data->{$prop} = $now->format(DateTimeInterface::ATOM);
        }

        return new self(
            self::assertObjectPropertyIsNonEmptyString($data, 'id'),
            self::optionalNonEmptyStringProperty($data, 'uid'),
            self::assertObjectPropertyIsNonEmptyString($data, 'type'),
            self::assertObjectPropertyIsNonEmptyString($data, 'lang'),
            self::assertObjectPropertyIsUtcDateTime($data, 'first_publication_date'),
            self::assertObjectPropertyIsUtcDateTime($data, 'last_publication_date'),
            self::optionalStringProperty($data, 'url'),
            array_values(self::assertObjectPropertyAllNonEmptyString($data, 'tags')),
            $translations,
            $body,
        );
    }

    #[Override]
    public function id(): string
    {
        return $this->id;
    }

    #[Override]
    public function uid(): string|null
    {
        return $this->uid;
    }

    #[Override]
    public function type(): string
    {
        return $this->type;
    }

    /** @inheritDoc */
    #[Override]
    public function tags(): iterable
    {
        return $this->tags;
    }

    #[Override]
    public function lang(): string
    {
        return $this->lang;
    }

    #[Override]
    public function firstPublished(): DateTimeInterface
    {
        return $this->firstPublished;
    }

    #[Override]
    public function lastPublished(): DateTimeInterface
    {
        return $this->lastPublished;
    }

    public function content(): FragmentCollection
    {
        return $this->body;
    }

    /** @inheritDoc */
    #[Override]
    public function translations(): iterable
    {
        return $this->translations;
    }

    #[Override]
    public function asLink(): DocumentLink
    {
        return DocumentLink::withDocument($this);
    }

    #[Override]
    public function data(): DocumentData
    {
        return $this;
    }

    #[Override]
    public function url(): string|null
    {
        return $this->url;
    }
}
