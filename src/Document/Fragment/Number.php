<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment;
use Prismic\Exception\InvalidArgument;
use function is_float;
use function is_int;

final class Number implements Fragment
{
    /** @var int|float */
    private $value;

    /** @param int|float $number */
    private function __construct($number)
    {
        $this->value = $number;
    }

    /** @param int|float $number */
    public static function new($number) : self
    {
        if (! is_int($number) && ! is_float($number)) {
            throw InvalidArgument::numberExpected($number);
        }

        return new static($number);
    }

    public function toInteger() : int
    {
        return (int) $this->value;
    }

    public function toFloat() : float
    {
        return (float) $this->value;
    }
}
