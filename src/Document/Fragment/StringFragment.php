<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment;
use Stringable;

final class StringFragment implements Fragment, Stringable
{
    private function __construct(private string $value)
    {
    }

    public static function new(string $value): self
    {
        return new self($value);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function isEmpty(): bool
    {
        return $this->value === '';
    }
}
