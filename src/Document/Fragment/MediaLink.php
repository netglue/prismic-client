<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment;

final class MediaLink implements Fragment
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
    ) : self {
        return new static($url, $fileName, $fileSize);
    }
}
