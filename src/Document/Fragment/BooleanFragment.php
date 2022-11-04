<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment;

final class BooleanFragment implements Fragment
{
    private function __construct(private bool $value)
    {
    }

    public static function new(bool $value): self
    {
        return new self($value);
    }

    public function __invoke(): bool
    {
        return $this->value;
    }

    public function isEmpty(): bool
    {
        return false;
    }
}
