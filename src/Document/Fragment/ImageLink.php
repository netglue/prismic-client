<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment;
use Prismic\UrlLink;

final class ImageLink implements Fragment, UrlLink
{
    /** @var string */
    private $url;
    /** @var string */
    private $fileName;
    /** @var int */
    private $fileSize;
    /** @var int */
    private $width;
    /** @var int */
    private $height;

    private function __construct(
        string $url,
        string $fileName,
        int $fileSize,
        int $width,
        int $height
    ) {
        $this->url = $url;
        $this->fileName = $fileName;
        $this->fileSize = $fileSize;
        $this->width = $width;
        $this->height = $height;
    }

    public static function new(
        string $url,
        string $fileName,
        int $fileSize,
        int $width,
        int $height
    ): self {
        return new static($url, $fileName, $fileSize, $width, $height);
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

    public function width(): int
    {
        return $this->width;
    }

    public function height(): int
    {
        return $this->height;
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
