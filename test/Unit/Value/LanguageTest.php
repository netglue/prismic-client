<?php
declare(strict_types=1);

namespace PrismicTest\Value;

use Prismic\Value\Language;
use PrismicTest\Framework\TestCase;

class LanguageTest extends TestCase
{
    public function testExpectedBehaviour() : void
    {
        $lang = Language::new('foo', 'bar');
        $this->assertSame('foo', $lang->id());
        $this->assertSame('bar', $lang->name());
    }
}
