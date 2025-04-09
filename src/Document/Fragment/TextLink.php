<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Override;
use Prismic\Document\Fragment;
use Prismic\Link;

final readonly class TextLink implements Fragment, Link
{
    /** @param non-empty-string $text */
    private function __construct(
        public string $text,
        public Link $link,
    ) {
    }

    /** @param non-empty-string $text */
    public static function new(
        string $text,
        Link $link,
    ): self {
        return new self($text, $link);
    }

    #[Override]
    public function isEmpty(): bool
    {
        return false;
    }

    #[Override]
    public function __toString(): string
    {
        return (string) $this->link;
    }
}
