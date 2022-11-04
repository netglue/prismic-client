<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment;
use Prismic\UrlLink;

final class MediaLink implements Fragment, UrlLink
{
    private function __construct(
        private string $url,
        private string $fileName,
        private int $fileSize,
    ) {
    }

    public static function new(
        string $url,
        string $fileName,
        int $fileSize,
    ): self {
        return new self($url, $fileName, $fileSize);
    }

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

    public function isEmpty(): bool
    {
        return false;
    }
}
