<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment;
use Prismic\UrlLink;

final class MediaLink implements Fragment, UrlLink
{
    /** @var string */
    private $url;
    /** @var string */
    private $fileName;
    /** @var int */
    private $fileSize;

    private function __construct(
        string $url,
        string $fileName,
        int $fileSize
    ) {
        $this->url = $url;
        $this->fileName = $fileName;
        $this->fileSize = $fileSize;
    }

    public static function new(
        string $url,
        string $fileName,
        int $fileSize
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
