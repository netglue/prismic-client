<?php

declare(strict_types=1);

namespace PrismicTest\Document\Fragment;

use Prismic\Document\Fragment\GeoPoint;
use PrismicTest\Framework\TestCase;

class GeoPointTest extends TestCase
{
    public function testConstructor(): GeoPoint
    {
        $point = GeoPoint::new(1.234, 5.678);
        $this->expectNotToPerformAssertions();

        return $point;
    }

    /** @depends testConstructor */
    public function testLatitudeIsExpectedValue(GeoPoint $point): void
    {
        self::assertEquals(1.234, $point->latitude());
    }

    /** @depends testConstructor */
    public function testLongitudeIsExpectedValue(GeoPoint $point): void
    {
        self::assertEquals(5.678, $point->longitude());
    }

    /** @depends testConstructor */
    public function testThatGeoPointsAreNotConsideredEmpty(GeoPoint $point): void
    {
        self::assertFalse($point->isEmpty());
    }

    public function testThatGeoPointsAreNotConsideredEmptyWithZeroValues(): void
    {
        $point = GeoPoint::new(0.0, 0.0);
        self::assertFalse($point->isEmpty());
    }

    public function testThatCastingToStringYieldsExpectedFormat(): void
    {
        $point = GeoPoint::new(1.23, 3.21);
        self::assertStringMatchesFormat('%f,%f', (string) $point);
    }

    public function testThatStringRepresentationUsesSixDecimalPlaces(): void
    {
        $point = GeoPoint::new(1.00000123, 1.00000123);
        self::assertEquals('1.000001,1.000001', (string) $point);
    }

    public function testThatStringRepresentationPreservesSign(): void
    {
        $point = GeoPoint::new(-1.0, -1.0);
        self::assertEquals('-1.000000,-1.000000', (string) $point);
    }
}
