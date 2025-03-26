<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Override;
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

    #[Override]
    public function __toString(): string
    {
        return $this->value;
    }

    #[Override]
    public function isEmpty(): bool
    {
        return $this->value === '';
    }
}
