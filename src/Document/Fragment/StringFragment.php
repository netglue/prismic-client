<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment;
use Stringable;

final class StringFragment implements Fragment, Stringable
{
    /** @var string */
    private $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function new(string $value) : self
    {
        return new static($value);
    }

    public function __toString() : string
    {
        return $this->value;
    }
}
