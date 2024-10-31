<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document;
use Prismic\Document\Fragment;
use Prismic\Link;

final class DocumentLink implements Fragment, Link
{
    /** @var string[] */
    private array $tags;

    /** @param string[] $tags */
    private function __construct(
        private string $id,
        private string|null $uid,
        private string $type,
        private string $lang,
        private bool $isBroken,
        iterable $tags,
        private string|null $url,
    ) {
        $this->tags = [];
        foreach ($tags as $tag) {
            $this->addTag($tag);
        }
    }

    private function addTag(string $tag): void
    {
        $this->tags[] = $tag;
    }

    /** @param string[] $tags */
    public static function new(
        string $id,
        string|null $uid,
        string $type,
        string $lang,
        bool $isBroken = false,
        iterable $tags = [],
        string|null $url = null,
    ): self {
        return new self($id, $uid, $type, $lang, $isBroken, $tags, $url);
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

    /** @return string[] */
    public function tags(): iterable
    {
        return $this->tags;
    }

    public function __toString(): string
    {
        return $this->id;
    }

    public function isEmpty(): bool
    {
        return false;
    }

    public function url(): string|null
    {
        return $this->url;
    }
}
