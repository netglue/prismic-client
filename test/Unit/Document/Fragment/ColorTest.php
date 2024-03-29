<?php

declare(strict_types=1);

namespace PrismicTest\Document\Fragment;

use Prismic\Document\Fragment\Color;
use Prismic\Exception\InvalidArgument;
use PrismicTest\Framework\TestCase;

use function hexdec;

class ColorTest extends TestCase
{
    public function testColorIsStringable(): void
    {
        $colour = Color::new('#000000');
        $this->assertSame('#000000', (string) $colour);
    }

    public function testColorIsValidated(): void
    {
        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('Expected a string that looks like a hex colour');
        Color::new('foo');
    }

    public function testAsRgb(): void
    {
        $colour = Color::new('#000000');
        $expect = [
            'r' => 0,
            'g' => 0,
            'b' => 0,
        ];
        $this->assertSame($expect, $colour->asRgb());
    }

    public function testAsRgbString(): void
    {
        $colour = Color::new('#000000');
        $this->assertSame('rgb(0, 0, 0)', $colour->asRgbString());
        $this->assertSame('rgba(0, 0, 0, 0.500)', $colour->asRgbString(.5));
    }

    public function testAsInteger(): void
    {
        $colour = Color::new('#000000');
        $expect = hexdec('000000');
        $this->assertSame($expect, $colour->asInteger());
    }

    /** @return array<array-key, array{0: string, 1: string}> */
    public static function inversions(): array
    {
        return [
            ['#ff0000', '#00ffff'],
        ];
    }

    /** @dataProvider inversions */
    public function testInversions(string $input, string $expect): void
    {
        $base = Color::new($input);
        $inverted = $base->invert();
        $this->assertSame($expect, (string) $inverted);
    }

    public function testThatColorsAreNotConsideredEmpty(): void
    {
        $colour = Color::new('#000000');
        $this->assertFalse($colour->isEmpty());
    }
}
