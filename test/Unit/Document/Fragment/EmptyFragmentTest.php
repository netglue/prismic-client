<?php

declare(strict_types=1);

namespace PrismicTest\Document\Fragment;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prismic\Document\Fragment\EmptyFragment;

class EmptyFragmentTest extends TestCase
{
    #[Test]
    public function anEmptyFragmentIsAlwaysConsideredEmpty(): void
    {
        $fragment = new EmptyFragment();
        self::assertTrue($fragment->isEmpty());
    }

    #[Test]
    public function anEmptyFragmentIsAnEmptyStringWhenCast(): void
    {
        $fragment = new EmptyFragment();
        self::assertEquals('', (string) $fragment);
    }
}
