<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment;
use Prismic\Exception\InvalidArgument;
use Stringable;
use function hexdec;
use function preg_match;
use function sprintf;
use function sscanf;
use function substr;

final class Color implements Fragment, Stringable
{
    /** @var string */
    private $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function new(string $value) : self
    {
        if (! self::isColor($value)) {
            throw InvalidArgument::invalidColor($value);
        }

        return new static($value);
    }

    /** @return int[] */
    public function asRgb() :? array
    {
        [$r, $g, $b] = sscanf($this->value, '#%02x%02x%02x');

        return [
            'r' => $r,
            'g' => $g,
            'b' => $b,
        ];
    }

    public function asRgbString(?float $alpha = null) :? string
    {
        ['r' => $r, 'g' => $g, 'b' => $b] = $this->asRgb();
        if ($alpha) {
            return sprintf('rgba(%d, %d, %d, %0.3f)', $r, $g, $b, $alpha);
        }

        return sprintf('rgb(%d, %d, %d)', $r, $g, $b);
    }

    private static function isColor(string $value) : bool
    {
        return (bool) preg_match('/^#[0-9A-F]{6,8}$/i', $value);
    }

    public function asInteger() :? int
    {
        return (int) hexdec(substr($this->value, 1));
    }

    public function __toString() : string
    {
        return $this->value;
    }
}
