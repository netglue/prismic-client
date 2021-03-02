<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment;
use Prismic\UrlLink;

final class WebLink implements Fragment, UrlLink
{
    /** @var string */
    private $url;
    /** @var string|null */
    private $target;

    private function __construct(
        string $url,
        ?string $target
    ) {
        $this->url = $url;
        $this->target = $target;
    }

    public static function new(string $url, ?string $target): self
    {
        return new self($url, $target);
    }

    public function url(): string
    {
        return $this->url;
    }

    public function target(): ?string
    {
        return $this->target;
    }

    public function __toString(): string
    {
        return $this->url;
    }

    public function isEmpty(): bool
    {
        return false;
    }
}
