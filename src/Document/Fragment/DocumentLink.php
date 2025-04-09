<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use DateTimeImmutable;
use Override;
use Prismic\Document;
use Prismic\Document\Fragment;
use Prismic\Link;
use Traversable;

use function array_filter;
use function array_map;
use function array_values;
use function iterator_to_array;

final readonly class DocumentLink implements Fragment, Link
{
    /** @var list<non-empty-string> */
    private array $tags;

    /**
     * @param non-empty-string         $id
     * @param non-empty-string|null    $uid
     * @param non-empty-string         $type
     * @param non-empty-string         $lang
     * @param array<array-key, string> $tags
     * @param non-empty-string|null    $slug
     */
    private function __construct(
        private string $id,
        private string|null $uid,
        private string $type,
        private string $lang,
        private bool $isBroken,
        array $tags,
        private string|null $slug = null,
        private DateTimeImmutable|null $firstPublicationDate = null,
        private DateTimeImmutable|null $lastPublicationDate = null,
    ) {
        $this->tags = array_values(array_filter(
            $tags,
            static fn (string $tag): bool => $tag !== '',
        ));
    }

    /**
     * @param non-empty-string            $id
     * @param non-empty-string|null       $uid
     * @param non-empty-string            $type
     * @param non-empty-string            $lang
     * @param iterable<array-key, string> $tags
     */
    public static function new(
        string $id,
        string|null $uid,
        string $type,
        string $lang,
        bool $isBroken = false,
        iterable $tags = [],
    ): self {
        $tags = $tags instanceof Traversable ? iterator_to_array($tags, false) : $tags;
        $tags = array_values(array_map(static function (mixed $tag): string {
            return $tag;
        }, $tags));

        return new self($id, $uid, $type, $lang, $isBroken, $tags);
    }

    /**
     * @param non-empty-string         $id
     * @param non-empty-string|null    $uid
     * @param non-empty-string         $type
     * @param non-empty-string         $lang
     * @param array<array-key, string> $tags
     * @param non-empty-string|null    $slug
     */
    public static function withExtendedInformation(
        string $id,
        string|null $uid,
        string $type,
        string $lang,
        bool $isBroken = false,
        array $tags = [],
        string|null $slug = null,
        DateTimeImmutable|null $firstPublicationDate = null,
        DateTimeImmutable|null $lastPublicationDate = null,
    ): self {
        return new self(
            $id,
            $uid,
            $type,
            $lang,
            $isBroken,
            $tags,
            $slug,
            $firstPublicationDate,
            $lastPublicationDate,
        );
    }

    public static function withDocument(Document $document): self
    {
        return new self(
            $document->id(),
            $document->uid(),
            $document->type(),
            $document->lang(),
            false,
            $document->tags(),
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

    public function language(): string
    {
        return $this->lang;
    }

    public function isBroken(): bool
    {
        return $this->isBroken;
    }

    /** @return list<string> */
    public function tags(): array
    {
        return $this->tags;
    }

    public function __toString(): string
    {
        return $this->id;
    }

    #[Override]
    public function isEmpty(): bool
    {
        return false;
    }

    public function firstPublished(): DateTimeImmutable|null
    {
        return $this->firstPublicationDate;
    }

    public function lastPublished(): DateTimeImmutable|null
    {
        return $this->lastPublicationDate;
    }

    /** @return non-empty-string|null */
    public function slug(): string|null
    {
        return $this->slug;
    }
}
