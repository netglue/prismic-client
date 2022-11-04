<?php

declare(strict_types=1);

namespace Prismic\Value;

/** @deprecated Bookmarks are deprecated - Removal in v2.0. */
final class Bookmark
{
    private function __construct(private string $name, private string $id)
    {
    }

    public static function new(string $name, string $id): self
    {
        return new self($name, $id);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function documentId(): string
    {
        return $this->id;
    }
}
