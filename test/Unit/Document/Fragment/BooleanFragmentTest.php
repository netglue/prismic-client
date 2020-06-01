<?php
declare(strict_types=1);

namespace PrismicTest\Document\Fragment;

use Prismic\Document\Fragment\BooleanFragment;
use PrismicTest\Framework\TestCase;

class BooleanFragmentTest extends TestCase
{
    /** @return bool[] */
    public function booleans() : iterable
    {
        return [
            'true' => [true],
            'false' => [false],
        ];
    }

    /** @dataProvider booleans */
    public function testConstructor(bool $value) : BooleanFragment
    {
        $frag = BooleanFragment::new($value);
        $this->addToAssertionCount(1);

        return $frag;
    }

    /** @dataProvider booleans */
    public function testInvoke(bool $value) : void
    {
        $bool = BooleanFragment::new($value);
        $this->assertSame($value, $bool());
    }

    /** @dataProvider booleans */
    public function testBooleansAreNotConsideredEmpty(bool $value) : void
    {
        $bool = BooleanFragment::new($value);
        $this->assertFalse($bool->isEmpty());
    }
}
