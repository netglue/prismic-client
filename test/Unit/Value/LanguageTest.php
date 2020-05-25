<?php
declare(strict_types=1);

namespace PrismicTest\Value;

use Prismic\Value\Language;
use PrismicTest\Framework\TestCase;
use function json_encode;
use const JSON_THROW_ON_ERROR;

class LanguageTest extends TestCase
{
    public function testExpectedBehaviour() : void
    {
        $lang = Language::new('foo', 'bar');
        $this->assertSame('foo', $lang->id());
        $this->assertSame('bar', $lang->name());
    }

    public function testLanguageCanBeSerializedToJson() : void
    {
        $value = json_encode(Language::new('a', 'b'), JSON_THROW_ON_ERROR);
        $this->assertEquals('{"id":"a","name":"b"}', $value);
    }
}
