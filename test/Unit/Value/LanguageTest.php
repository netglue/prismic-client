<?php

declare(strict_types=1);

namespace PrismicTest\Value;

use Prismic\Value\Language;
use PrismicTest\Framework\TestCase;

class LanguageTest extends TestCase
{
    public function testExpectedBehaviour(): void
    {
        $lang = Language::new('foo', 'bar');
        self::assertSame('foo', $lang->id());
        self::assertSame('bar', $lang->name());
    }

    public function testThatCastingToStringYieldsTheLanguageCode(): void
    {
        $language = Language::new('en-gb', 'The Queens English');
        self::assertEquals('en-gb', (string) $language);
    }
}
