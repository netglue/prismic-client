<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment;

final class ImageLink implements Fragment
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
    ) : self {
        return new static($url, $fileName, $fileSize, $width, $height);
    }
}
