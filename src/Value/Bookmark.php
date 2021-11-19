<?php

declare(strict_types=1);

namespace Prismic\Value;

/** @deprecated Bookmarks are deprecated - Removal in v2.0. */
final class Bookmark
{
    /** @var string */
    private $name;
    /** @var string */
    private $id;

    private function __construct(string $name, string $id)
    {
        $this->name = $name;
        $this->id = $id;
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
