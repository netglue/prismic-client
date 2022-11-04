<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment;
use Prismic\Exception\InvalidArgument;
use Stringable;

use function is_float;
use function is_int;

final class Number implements Fragment, Stringable
{
    private function __construct(private int|float $value)
    {
    }

    /** @param int|float $number */
    public static function new(mixed $number): self
    {
        /** @psalm-suppress DocblockTypeContradiction */
        if (! is_int($number) && ! is_float($number)) {
            throw InvalidArgument::numberExpected($number);
        }

        return new self($number);
    }

    public function isFloat(): bool
    {
        return is_float($this->value);
    }

    public function isInteger(): bool
    {
        return is_int($this->value);
    }

    /**
     * @return int|float
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     * @todo Add native type hint in 2.0.0
     */
    public function value()
    {
        return $this->value;
    }

    public function toInteger(): int
    {
        return (int) $this->value;
    }

    public function toFloat(): float
    {
        return (float) $this->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }

    public function isEmpty(): bool
    {
        return false;
    }
}
