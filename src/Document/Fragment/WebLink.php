<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Override;
use Prismic\Document\Fragment;
use Prismic\UrlLink;

final class WebLink implements Fragment, UrlLink
{
    private function __construct(
        private string $url,
        private string|null $target,
    ) {
    }

    public static function new(string $url, string|null $target): self
    {
        return new self($url, $target);
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
}
