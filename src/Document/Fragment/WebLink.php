<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Override;
use Prismic\Document\Fragment;
use Prismic\LinkVariant;
use Prismic\UrlLink;

final readonly class WebLink implements Fragment, UrlLink, LinkVariant
{
    /** @param non-empty-string|null $variant */
    private function __construct(
        private string $url,
        private string|null $target,
        private string|null $variant,
    ) {
    }

    /** @param non-empty-string|null $variant */
    public static function new(
        string $url,
        string|null $target,
        string|null $variant = null,
    ): self {
        return new self($url, $target, $variant);
    }

    #[Override]
    public function url(): string
    {
        return $this->url;
    }

    public function target(): string|null
    {
        return $this->target;
    }

    public function __toString(): string
    {
        return $this->url;
    }

    #[Override]
    public function isEmpty(): bool
    {
        return false;
    }

    /** @return non-empty-string|null */
    #[Override]
    public function variant(): string|null
    {
        return $this->variant;
    }
}
