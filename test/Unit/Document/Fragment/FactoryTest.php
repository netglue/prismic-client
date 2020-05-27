<?php
declare(strict_types=1);

namespace PrismicTest\Document\Fragment;

use Prismic\Document\Fragment\BooleanFragment;
use Prismic\Document\Fragment\Color;
use Prismic\Document\Fragment\DateFragment;
use Prismic\Document\Fragment\EmptyFragment;
use Prismic\Document\Fragment\Factory;
use Prismic\Document\Fragment\Number;
use Prismic\Document\Fragment\StringFragment;
use Prismic\Exception\InvalidArgument;
use Prismic\Json;
use PrismicTest\Framework\TestCase;

class FactoryTest extends TestCase
{
    /** @return mixed[] */
    public function scalarTypes() : iterable
    {
        return [
            'integer' => [1, Number::class],
            'float' => [0.123, Number::class],
            'bool' => [true, BooleanFragment::class],
            'string' => ['whatever', StringFragment::class],
            'null' => [null, EmptyFragment::class],
            'hex colour' => ['#000000', Color::class],
            'Y-m-d' => ['2020-01-01', DateFragment::class],
            'Date Time' => ['2020-01-01T10:00:00+00:00', DateFragment::class],
        ];
    }

    /**
     * @param mixed $value
     *
     * @dataProvider scalarTypes
     */
    public function testScalarValues($value, string $expectedType) : void
    {
        $fragment = Factory::factory($value);
        $this->assertInstanceOf($expectedType, $fragment);
    }

    public function testUnknownLinkTypeIsExceptional() : void
    {
        $link = Json::decodeObject('{
            "link_type": "Not Right"
        }');

        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('The link type "Not Right" is not a known type of link');
        Factory::factory($link);
    }
}
