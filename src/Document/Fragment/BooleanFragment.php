<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment;

final class BooleanFragment implements Fragment
{
    /** @var bool */
    private $value;

    private function __construct(bool $value)
    {
        $this->value = $value;
    }

    public static function new(bool $value) : self
    {
        return new static($value);
    }

    public function __invoke() : bool
    {
        return $this->value;
    }
}
