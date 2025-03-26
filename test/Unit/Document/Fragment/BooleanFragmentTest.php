<?php

declare(strict_types=1);

namespace PrismicTest\Document\Fragment;

use PHPUnit\Framework\Attributes\DataProvider;
use Prismic\Document\Fragment\BooleanFragment;
use PrismicTest\Framework\TestCase;

final class BooleanFragmentTest extends TestCase
{
    /** @return array<string, bool[]> */
    public static function booleans(): array
    {
        return [
            'true' => [true],
            'false' => [false],
        ];
    }

    #[DataProvider('booleans')]
    public function testInvoke(bool $value): void
    {
        $bool = BooleanFragment::new($value);
        $this->assertSame($value, $bool());
    }

    #[DataProvider('booleans')]
    public function testBooleansAreNotConsideredEmpty(bool $value): void
    {
        $bool = BooleanFragment::new($value);
        $this->assertFalse($bool->isEmpty());
    }
}
