<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment;

final class WebLink implements Fragment
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

    public static function new(string $url, ?string $target) : self
    {
        return new static($url, $target);
    }

    public function url() : string
    {
        return $this->url;
    }

    public function target() :? string
    {
        return $this->target;
    }
}
