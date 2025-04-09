<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use DateTimeImmutable;
use Override;
use Prismic\Document;
use Prismic\Document\Fragment;
use Prismic\Link;
use Prismic\LinkVariant;
use Traversable;

use function array_filter;
use function array_map;
use function array_values;
use function iterator_to_array;

final readonly class DocumentLink implements Fragment, Link, LinkVariant
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
     * @param non-empty-string|null    $variant
     */
    private function __construct(
        private string $id,
        private string|null $uid,
        private string $type,
        private string $lang,
        private bool $isBroken,
        array $tags,
        private string|null $url,
        private string|null $slug = null,
        private DateTimeImmutable|null $firstPublicationDate = null,
        private DateTimeImmutable|null $lastPublicationDate = null,
        private string|null $variant = null,
    ) {
        $this->tags = array_values(array_filter(
            $tags,
            static fn (string $tag): bool => $tag !== '',
        ));
    }

    /**
     * @param non-empty-string         $id
     * @param non-empty-string|null    $uid
     * @param non-empty-string         $type
     * @param non-empty-string         $lang
     * @param array<array-key, string> $tags
     * @param non-empty-string|null    $variant
     */
    public static function new(
        string $id,
        string|null $uid,
        string $type,
        string $lang,
        bool $isBroken = false,
        array $tags = [],
        string|null $url = null,
        string|null $variant = null,
    ): self {
        return new self($id, $uid, $type, $lang, $isBroken, $tags, $url, null, null, null, $variant);
    }

    /**
     * @param non-empty-string         $id
     * @param non-empty-string|null    $uid
     * @param non-empty-string         $type
     * @param non-empty-string         $lang
     * @param array<array-key, string> $tags
     * @param non-empty-string|null    $slug
     * @param non-empty-string|null    $variant
     */
    public static function withExtendedInformation(
        string $id,
        string|null $uid,
        string $type,
        string $lang,
        bool $isBroken = false,
        array $tags = [],
        string|null $url = null,
        string|null $slug = null,
        DateTimeImmutable|null $firstPublicationDate = null,
        DateTimeImmutable|null $lastPublicationDate = null,
        string|null $variant = null,
    ): self {
        return new self(
            $id,
            $uid,
            $type,
            $lang,
            $isBroken,
            $tags,
            $url,
            $slug,
            $firstPublicationDate,
            $lastPublicationDate,
            $variant,
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
            $document->url(),
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

    public function url(): string|null
    {
        return $this->url;
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

    /** @return non-empty-string|null */
    #[Override]
    public function variant(): string|null
    {
        return $this->variant;
    }
}
