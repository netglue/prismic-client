<?php

declare(strict_types=1);

namespace PrismicTest\Document\Fragment;

use Prismic\Document\Fragment\BooleanFragment;
use PrismicTest\Framework\TestCase;

class BooleanFragmentTest extends TestCase
{
    /** @return array<string, bool[]> */
    public static function booleans(): array
    {
        return [
            'true' => [true],
            'false' => [false],
        ];
    }

    /** @dataProvider booleans */
    public function testInvoke(bool $value): void
    {
        $bool = BooleanFragment::new($value);
        $this->assertSame($value, $bool());
    }

    /** @dataProvider booleans */
    public function testBooleansAreNotConsideredEmpty(bool $value): void
    {
        $bool = BooleanFragment::new($value);
        $this->assertFalse($bool->isEmpty());
    }
}
