<?php

declare(strict_types=1);

namespace PrismicTest\Document\Fragment;

use Prismic\Document\Fragment\Number;
use Prismic\Exception\InvalidArgument;
use PrismicTest\Framework\TestCase;

class NumberTest extends TestCase
{
    public function testNonNumbersAreExceptional(): void
    {
        $this->expectException(InvalidArgument::class);
        Number::new('foo');
    }

    public function testConstructor(): Number
    {
        $number = Number::new(1);
        $this->addToAssertionCount(1);

        return $number;
    }

    /** @depends testConstructor */
    public function testValueIsExpectedValue(Number $number): void
    {
        $this->assertSame(1, $number->value());
    }

    /** @depends testConstructor */
    public function testToIntegerIsExpectedValue(Number $number): void
    {
        $this->assertSame(1, $number->toInteger());
    }

    /** @depends testConstructor */
    public function testToFloatIsExpectedValue(Number $number): void
    {
        $this->assertSame(1.0, $number->toFloat());
    }

    /** @return mixed[] */
    public function numberProvider(): iterable
    {
        return [
            '1' => [Number::new(1)],
            '1.0' => [Number::new(1.0)],
            '0' => [Number::new(0)],
            '0.0' => [Number::new(0.0)],
            '-10' => [Number::new(-10)],
            '-10.1' => [Number::new(-10.1)],
        ];
    }

    /** @dataProvider numberProvider */
    public function testANumberIsNotConsideredEmpty(Number $number): void
    {
        $this->assertFalse($number->isEmpty());
    }

    public function testThatAFloatIsAFloatAndAnIntIsAnInt(): void
    {
        $this->assertTrue(Number::new(1)->isInteger());
        $this->assertTrue(Number::new(1.0)->isFloat());
        $this->assertFalse(Number::new(1.0)->isInteger());
        $this->assertFalse(Number::new(1)->isFloat());
    }
}
