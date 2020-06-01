<?php
declare(strict_types=1);

namespace PrismicTest\Document\Fragment;

use Prismic\Document\Fragment\GeoPoint;
use PrismicTest\Framework\TestCase;

class GeoPointTest extends TestCase
{
    public function testConstructor() : GeoPoint
    {
        $point = GeoPoint::new(1.234, 5.678);
        $this->addToAssertionCount(1);

        return $point;
    }

    /** @depends testConstructor */
    public function testLatitudeIsExpectedValue(GeoPoint $point) : void
    {
        $this->assertEquals(1.234, $point->latitude());
    }

    /** @depends testConstructor */
    public function testLongitudeIsExpectedValue(GeoPoint $point) : void
    {
        $this->assertEquals(5.678, $point->longitude());
    }

    /** @depends testConstructor */
    public function testThatGeoPointsAreNotConsideredEmpty(GeoPoint $point) : void
    {
        $this->assertFalse($point->isEmpty());
    }
}
