<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document;
use Prismic\Document\Fragment;
use Prismic\Link;

final class DocumentLink implements Fragment, Link
{
    /** @var string[] */
    private $tags;
    /** @var string */
    private $id;
    /** @var string|null */
    private $uid;
    /** @var string */
    private $type;
    /** @var string */
    private $lang;
    /** @var bool */
    private $isBroken;
    /** @var string|null */
    private $url;

    /** @param string[] $tags */
    private function __construct(
        string $id,
        ?string $uid,
        string $type,
        string $lang,
        bool $isBroken,
        iterable $tags,
        ?string $url
    ) {
        $this->id = $id;
        $this->uid = $uid;
        $this->type = $type;
        $this->lang = $lang;
        $this->isBroken = $isBroken;
        $this->url = $url;
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
        ?string $uid,
        string $type,
        string $lang,
        bool $isBroken = false,
        iterable $tags = [],
        ?string $url = null
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
            $document->url()
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function uid(): ?string
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

    public function url(): ?string
    {
        return $this->url;
    }
}
