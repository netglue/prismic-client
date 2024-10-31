<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment;
use Prismic\Exception\InvalidArgument;
use Stringable;

use function array_map;
use function assert;
use function count;
use function dechex;
use function hexdec;
use function implode;
use function is_array;
use function preg_match;
use function sprintf;
use function sscanf;
use function substr;

final class Color implements Fragment, Stringable
{
    private function __construct(private string $value)
    {
    }

    public static function new(string $value): self
    {
        if (! self::isColor($value)) {
            throw InvalidArgument::invalidColor($value);
        }

        return new self($value);
    }

    /** @return array{r: int, g: int, b: int} */
    public function asRgb(): array
    {
        $parts = sscanf($this->value, '#%02x%02x%02x');
        assert(is_array($parts));
        assert(count($parts) === 3);
        [$r, $g, $b] = $parts;

        return [
            'r' => (int) $r,
            'g' => (int) $g,
            'b' => (int) $b,
        ];
    }

    public function asRgbString(float|null $alpha = null): string|null
    {
        ['r' => $r, 'g' => $g, 'b' => $b] = $this->asRgb();
        if ($alpha !== null) {
            return sprintf('rgba(%d, %d, %d, %0.3f)', $r, $g, $b, $alpha);
        }

        return sprintf('rgb(%d, %d, %d)', $r, $g, $b);
    }

    private static function isColor(string $value): bool
    {
        return (bool) preg_match('/^#[0-9A-F]{6,8}$/i', $value);
    }

    public function asInteger(): int|null
    {
        return (int) hexdec(substr($this->value, 1));
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function invert(): self
    {
        $parts = array_map(static function (int $channel): string {
            return sprintf('%02s', dechex(255 - $channel));
        }, $this->asRgb());

        return self::new(sprintf('#%s', implode('', $parts)));
    }

    public function isEmpty(): bool
    {
        return false;
    }
}
