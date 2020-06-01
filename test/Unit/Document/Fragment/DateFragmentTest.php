<?php
declare(strict_types=1);

namespace PrismicTest\Document\Fragment;

use Prismic\Document\Fragment\DateFragment;
use Prismic\Exception\InvalidArgument;
use PrismicTest\Framework\TestCase;

class DateFragmentTest extends TestCase
{
    public function testThatDateFragmentsAreNotConsideredEmpty() : void
    {
        $date = DateFragment::day('2020-01-01');
        $this->assertFalse($date->isEmpty());
    }

    public function testDayFactoryExceptionForInvalidFormat() : void
    {
        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('Expected a date value in the format Y-m-d but received "foo"');
        DateFragment::day('foo');
    }

    public function testDateAtomFactoryExceptionForInvalidFormat() : void
    {
        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('but received "foo"');
        DateFragment::fromAtom('foo');
    }
}
