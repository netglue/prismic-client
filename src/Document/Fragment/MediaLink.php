<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Override;
use Prismic\Document\Fragment;
use Prismic\LinkVariant;
use Prismic\UrlLink;

final readonly class MediaLink implements Fragment, UrlLink, LinkVariant
{
    /** @param non-empty-string|null $variant */
    private function __construct(
        private string $url,
        private string $fileName,
        private int $fileSize,
        private string|null $variant,
    ) {
    }

    /** @param non-empty-string|null $variant */
    public static function new(
        string $url,
        string $fileName,
        int $fileSize,
        string|null $variant = null,
    ): self {
        return new self($url, $fileName, $fileSize, $variant);
    }

    #[Override]
    public function url(): string
    {
        return $this->url;
    }

    public function filename(): string
    {
        return $this->fileName;
    }

    public function filesize(): int
    {
        return $this->fileSize;
    }

    public function __toString(): string
    {
        return $this->fileName;
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
