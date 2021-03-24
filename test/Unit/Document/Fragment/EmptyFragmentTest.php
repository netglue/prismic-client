<?php

declare(strict_types=1);

namespace PrismicTest\Document\Fragment;

use PHPUnit\Framework\TestCase;
use Prismic\Document\Fragment\EmptyFragment;

class EmptyFragmentTest extends TestCase
{
    /** @test */
    public function anEmptyFragmentIsAlwaysConsideredEmpty(): void
    {
        $fragment = new EmptyFragment();
        self::assertTrue($fragment->isEmpty());
    }

    /** @test */
    public function anEmptyFragmentIsAnEmptyStringWhenCast(): void
    {
        $fragment = new EmptyFragment();
        self::assertEquals('', (string) $fragment);
    }
}
